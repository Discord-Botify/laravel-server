<?php


namespace App\Http\Services;


use App\Jobs\ProcessArtistEntries;
use App\Models\AppSession;
use App\Models\User;
use http\Exception\InvalidArgumentException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\SpotifyWebAPIException;

class SpotifyService
{
    private static $session;
    private static $api;
    private $access_token;
    private $refresh_token;
    private $user_id;

    public function __construct()
    {
        // Register the app with Spotify
        if(self::$session == null)
        {
            self::$session = new Session(
                env('SPOTIFY_CLIENT_ID'),
                env('SPOTIFY_CLIENT_SECRET'),
                env('SPOTIFY_REDIRECT_URI'),
            );
        }

        if(self::$api == null)
        {
            self::$api = new SpotifyWebAPI();
        }
    }

    public function loadClientCredentials()
    {
        self::$session->requestCredentialsToken();
        $this->access_token = self::$session->getAccessToken();
        self::$api->setAccessToken($this->access_token);
    }

    public function loadUser()
    {
        // Get the user from auth
        $user_id = AppSession::id();

        $user = User::where('user_id', $user_id)->first();
        if($user == null)
        {
            throw new InvalidArgumentException('could find the provided user');
        }

        $this->user_id = $user->user_id;
        $this->setAccessToken($user->spotify_access_token);
    }

    public function getAccessToken(string $auth_code = null): string
    {
        if(blank($this->access_token))
        {
            if(blank($auth_code))
            {
                throw new InvalidArgumentException('Access Token has not been gotten yet, please provide a auth code from Spotify');
            }

            self::$session->requestAccessToken($auth_code);
            $this->access_token = self::$session->getAccessToken();
            $this->refresh_token = self::$session->getRefreshToken();
            self::$api->setAccessToken($this->access_token);
        }

        return $this->access_token;
    }

    public function getUserDetails()
    {
        $this->checkAccessTokenStatus();

        return self::$api->me();
    }

    public function setAccessToken(string $access_token): self
    {
        // Check if this particular access_token is expired or not
        $user = User::where('spotify_access_token', $access_token)->first();
        if($user->isTokenExpired())
        {
            $access_token = $this->getRefreshedAccessToken($user->spotify_refresh_token);
            $user->spotify_access_token = $access_token;
            $user->spotify_access_token_expiration = now()->addHour();
            $user->save();
        }

        $this->access_token = $access_token;
        self::$api->setAccessToken($access_token);
        return $this;
    }

    public function getRefreshedAccessToken(string $refresh_token)
    {
        self::$session->setRefreshToken($refresh_token);
        self::$session->refreshAccessToken($this->refresh_token);
        $access_token = self::$session->getAccessToken();
        return $access_token;
    }

    private function checkAccessTokenStatus(): bool
    {
        if(blank($this->access_token))
        {
            throw new SpotifyWebAPIException('Access token is not set');
        }

        return true;
    }

    public function getRefreshToken()
    {
        if(blank($this->refresh_token))
        {
            throw new SpotifyWebAPIException('Refresh token is not set');
        }

        return $this->refresh_token;
    }

    public function getAuthLink()
    {
        $options = [
            'scope' => [
                'user-follow-read',
                'user-read-email',
            ]
        ];

        return self::$session->getAuthorizeUrl($options);
    }

    public function getUserFollowedArtistsFromSpotify()
    {
        // Don't allow this to run if a user hasn't been set
        if($this->user_id == null)
        {
            throw new InvalidArgumentException('User has not been set');
        }

        // Get the list of all of the user's followed artists. It has to be batched in groups of 50
        $artists = new Collection();
        $artists_batch = [];
        $limit = config('app.env') == 'production' ? 50 : 50;
        $after = null;
        do
        {
            $artists_batch = self::$api->getUserFollowedArtists([
                'type' => 'artist',
                'limit' => $limit,
                'after' => $after
            ]);
            $artists->push($artists_batch->artists->items);
            // Get the last artist from the request so that we can get the $after
            $after = $artists_batch->artists->cursors->after;
        } while (sizeof($artists_batch->artists->items) == 50);

        // Map into the format that will enter our DB
        $artists = $artists->flatten()->mapWithKeys(function($artist)
        {
            return [
                $artist->id => [
                    'artist_id' => $artist->id,
                    'artist_name' => $artist->name,
                    'artist_href' => $artist->href,
                    'artist_uri' => $artist->uri,
                ]
            ];
        });

        // Put the artists/user relationship in the DB NOW so we don't get delays in the DB loading if the user refreshes the page fast
        $artist_ids = $artists->keys();
        $user = User::find($this->user_id);
        $user->followed_artists()->sync($artist_ids);


        // Send the artist to the Database process to add them to the DB
        if(config('app.env') == 'local')
        {
            Log::info("manual run of process artist entries");
            $job = new ProcessArtistEntries($artists, $this->user_id);
            $job->handle();
        }
        else
        {
            Log::info("Dispatching job for process artist entries");
            ProcessArtistEntries::dispatch($artists, $this->user_id);
        }

        return $artists->sortBy('artist_name');
    }

    public function getUserFollowedArtistsFromDB()
    {
        // Don't allow this to run if a user hasn't been set
        if($this->user_id == null)
        {
            throw new InvalidArgumentException('User has not been set');
        }

        $user = User::with('followed_artists')->find($this->user_id);
        return $user->followed_artists->keyBy('artist_name')->sortBy('artist_name');
    }

    public function getArtistsAlbums(string $artist_id): Collection
    {
        // Get the list of all of the artist's albums. It has to be batched in groups of 50
        $albums = new Collection();
        $albums_batch = [];
        $limit = config('app.env') == 'production' ? 50 : 50;
        $offset = null;
        do
        {
            $albums_batch = self::$api->getArtistAlbums($artist_id, [
                'include_groups' => config('custom.album_include_groups'),
                'country' => 'US',
                'limit' => $limit,
                'offset' => $offset
            ]);

            // Sleep .1 seconds each batch in an attempt to reduce the rate limiter
            usleep(100000);

            $albums->push($albums_batch->items);
            // Get the last artist from the request so that we can get the $after
            $offset += 50;
        } while (sizeof($albums_batch->items) == 50);

        // Return the list sorted with the most recent release at the top
        return $albums->flatten()->sortByDesc(function ($album) {
            return $album->release_date;
        })->mapWithKeys(function ($album) {
            return [
                $album->id => [
                    'album_id' => $album->id,
                    'album_group' => $album->album_group,
                    'album_type' => $album->album_type,
                    'album_name' => $album->name,
                    'album_href' => $album->href,
                    'album_uri' => $album->uri,
                ]
            ];
        });

    }
}
