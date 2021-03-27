<?php

namespace App\Console\Commands;

use App\Http\Services\SpotifyService;
use App\Models\FollowedArtist;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QueueNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:queue_notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Go through all the artists in our database and look if they have new albums';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info("Starting the Notifications Creation job for " . now()->toDateTimeString());
        // Load all artists from the database with their corresponding followers
        $followed_artists = FollowedArtist::with('users')
            ->get();

        // For each artist, pull their albums from the Spotify API
        $spotify_service = new SpotifyService();
        $spotify_service->loadClientCredentials();
        foreach ($followed_artists as $followed_artist)
        {
            $albums = $spotify_service->getArtistsAlbums($followed_artist->artist_id);

            // If the artist's last album is different than artist_last_album_id, maybe they have a release
            if($albums->first()['album_id'] != $followed_artist->artist_last_album_id)
            {
                $this->makeAlbumNotifications($followed_artist, $albums);
            }

            // Update the artist with the new album data, in case some albums were removed or something
            $followed_artist->artist_album_count = $albums->count();
            $followed_artist->artist_last_album_id = $albums->first()['album_id'];
            $followed_artist->artist_last_album_date = $albums->first()['album_release_date'];
            $followed_artist->save();
        }

        Log::info("Ending the Notifications job");
    }

    private function makeAlbumNotifications($followed_artist, $albums)
    {
        $albums = $albums->reverse();

        // Get the albums after the saved most recent release in the Database
        $new_releases = $albums->skipUntil(function ($album) use ($followed_artist)
        {
            return $album['id'] == $followed_artist->artist_last_album_id;
        })->skip(1);

        // If new releases is empty, perhaps the most recent release we have saved was removed from Spotify. In that case, grab the releases that have release dates after what we have saved in the DB
        if($new_releases->isEmpty())
        {
            $new_releases = $albums->skipUntil(function ($album) use ($followed_artist)
            {
                return $album['album_release_date'] > $followed_artist->artist_last_album_date;
            });
        }

        if($new_releases->isEmpty()) return;

        // At this point, we are confident that there are new releases! Let's send notifications for them
        Log::info("sending notifications for " . $followed_artist->artist_name);
        foreach ($new_releases as $new_release)
        {
            foreach ($followed_artist->users as $user)
            {
                Notification::create([
                    'notification_id' => Str::uuid(),
                    'notification_sent' => 0,
                    'notification_dismissed' => 0,
                    'user_id_to' => $user->user_id,
                    'artist_name' => $followed_artist->artist_name,
                    'album_type' => $new_release['album_type'],
                    'album_name' => $new_release['album_name'],
                    'album_href' => $new_release['album_href'],
                    'album_uri' => $new_release['album_uri'],
                ]);
            }
        }

    }
}
