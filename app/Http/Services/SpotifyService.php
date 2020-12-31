<?php


namespace App\Http\Services;


use App\Jobs\ProcessPlaylistEntries;
use App\Models\AppPlaylist;
use App\Models\AppPlaylistAppSong;
use App\Models\AppSession;
use App\Models\AppSong;
use App\Models\User;
use http\Exception\InvalidArgumentException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\SpotifyWebAPIException;

class SpotifyService
{
    private $session;
    private $api;
    private $access_token;
    private $refresh_token;
    private $user_id;

    public function __construct()
    {
        // Register the app with Spotify
        $this->session = new Session(
            env('SPOTIFY_CLIENT_ID'),
            env('SPOTIFY_CLIENT_SECRET'),
            env('SPOTIFY_REDIRECT_URI'),
        );

        $this->api = new SpotifyWebAPI();
    }

    public function getAccessToken(string $auth_code = null): string
    {
        if(blank($this->access_token))
        {
            if(blank($auth_code))
            {
                throw new InvalidArgumentException('Access Token has not been gotten yet, please provide a auth code from Spotify');
            }

            $this->session->requestAccessToken($auth_code);
            $this->access_token = $this->session->getAccessToken();
            $this->refresh_token = $this->session->getRefreshToken();
            $this->api->setAccessToken($this->access_token);
        }

        return $this->access_token;
    }

    public function getUserDetails()
    {
        $this->checkAccessTokenStatus();

        return $this->api->me();
    }

    public function setUser()
    {
        // Get the user from auth
        $user_id = AppSession::getLoggedInUserId();

        $user = User::where('user_id', $user_id)->first();
        if($user == null)
        {
            throw new InvalidArgumentException('could find the provided user');
        }

        $this->user_id = $user->user_id;
        $this->setAccessToken($user->spotify_access_token);
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
        $this->api->setAccessToken($access_token);
        return $this;
    }

    public function getRefreshedAccessToken(string $refresh_token)
    {
        $this->session->setRefreshToken($refresh_token);
        $this->session->refreshAccessToken($this->refresh_token);
        $access_token = $this->session->getAccessToken();
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
                'playlist-read-private',
                'user-read-email',
                'app-remote-control'
            ]
        ];

        return $this->session->getAuthorizeUrl($options);
    }

    public function getUserPlaylistsFromSpotify()
    {
        // Don't allow this to run if a user hasn't been set
        if($this->user_id == null)
        {
            throw new InvalidArgumentException('User has not been set');
        }

        // Get the list of all of the user's playlists. It has to be batched in groups of 50
        $playlists = new Collection();
        $playlists_batch = [];
        $limit = env('APP_ENV') == 'production' ? 50 : 2;
        $offset = 0;
        do
        {
            $playlists_batch = $this->api->getMyPlaylists([
                'limit' => $limit,
                'offset' => $offset
            ]);
            $playlists->push($playlists_batch->items);
            $offset += 50;
        } while (sizeof($playlists_batch->items) == 50);

        // Map the playlists into the data format that's going to be in our DB
        $playlists = collect($playlists)->flatten()->mapWithKeys(function ($item)
        {
            return [
                $item->id => [
                    'user_id' => $this->user_id,
                    'spotify_playlist_id' => $item->id,
                    'playlist_name' => $item->name,
                    'playlist_image_url' => $item->images[0]->url,
                ]
            ];
        });

        // For each playlist, we have to grab the songs
        foreach ($playlists as $id => $playlist)
        {
            // Once again, gotta batch. This time we can grab 100 at a time
            $songs = new Collection();
            $songs_batch = [];
            $offset = 0;
            do
            {
                $songs_batch = $this->api->getPlaylistTracks($id, [
                    'limit' => 100,
                    'offset' => $offset,
                ]);
                $songs->push($songs_batch->items);
                $offset += 100;
            } while (sizeof($songs_batch->items) == 100);

            // Map the songs into the format we need and put them in the playlist array
            $formatted_songs = $songs->flatten()->mapWithKeys(function ($song) use ($playlist)
            {
                // Grab all the artists and format a string for them
                $artists_string = '';
                foreach ($song->track->artists as $artist)
                {
                    $artists_string .= ($artist->name . ', ');
                }
                $artists_string = Str::replaceLast(', ', '', $artists_string);
                return [
                    $song->track->id => [
                        'spotify_song_id' => $song->track->id,
                        'song_name' => $song->track->name,
                        'song_duration_ms' => $song->track->duration_ms,
                        'song_album_art_url' => $song->track->album->images[0]->url,
                        'artist_name' => $artists_string,
                    ]
                ];
            });
            $playlist['songs'] = $formatted_songs;
            $playlists->put($id, $playlist);
        }

        // Start the process that puts all the playlists in the database
        ProcessPlaylistEntries::dispatch($playlists, $this->user_id);

        // This can be commented out to test the functionality in the current process
//        $this->processPlaylistEntriesTest($playlists, $this->user_id);

        return $playlists;
    }

    private function processPlaylistEntriesTest(Collection $playlists, string $user_id)
    {
        // Delete all playlists and relationships in the DB for this user that are not in this current batch
        // TODO deleting for now, perhaps we disable later?
        $playlist_ids = $playlists->pluck('spotify_playlist_id');
        $playlists_to_delete_query = AppPlaylist::where('user_id', $this->user_id)->whereNotIn('spotify_playlist_id', $playlist_ids);
        $playlist_ids_to_delete = $playlists_to_delete_query->get()->pluck('app_playlist_id');
        AppPlaylistAppSong::whereIn('app_playlist_id', $playlist_ids_to_delete)->delete();
        $playlists_to_delete_query->delete();


        foreach ($playlists as $playlist)
        {
            // Save the playlist in the DB
            $db_playlist = AppPlaylist::updateOrCreate(
                [
                    'user_id' => $this->user_id,
                    'spotify_playlist_id' => $playlist['spotify_playlist_id']
                ],
                [
                    'playlist_name' => $playlist['playlist_name'],
                    'playlist_image_url' => $playlist['playlist_image_url'],
                ]
            );

            // Save the songs in the DB
            AppSong::upsert($playlist['songs']->toArray(), ['spotify_song_id'], ['song_name', 'song_duration_ms', 'song_album_art_url', 'artist_name']);

            // update the DB playlist with it's relationship with the songs
            $song_ids = $playlist['songs']->pluck('spotify_song_id')->toArray();
            $db_playlist->songs()->sync($song_ids);
        }

    }

}
