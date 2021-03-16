<?php

namespace App\Console\Commands;

use App\Http\Services\SpotifyService;
use App\Models\FollowedArtist;
use App\Models\Notification;
use Illuminate\Console\Command;
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
    protected $description = 'Command description';

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
        // Load all artists from the database with their corresponding followers
        $followed_artists = FollowedArtist::with('users')
            ->when(env('APP_ENV') != 'production', function ($query)
            {
//                $query->take(5);
            })
            ->get();

        // For each artist, pull their albums from the Spotify API
        $spotify_service = new SpotifyService();
        $spotify_service->loadClientCredentials();
        foreach ($followed_artists as $followed_artist)
        {
            $albums = $spotify_service->getArtistsAlbums($followed_artist->artist_id);

            // If the artist's last released album is different than artist_last_album_id, they have a new release!
            if($albums->first()['album_id'] != $followed_artist->artist_last_album_id)
            {
                dump("sending notifications for " . $followed_artist->artist_name);
                // Determine the number of new releases by subtracting their old artist_album_count from the new album count
                // TODO maybe we need to instead grab the albums after the saved recent release in case there were removed albums with the added ones? BIG MAYBE
                $new_album_count = $albums->count() - $followed_artist->artist_album_count;

                // Grab the number of new releases and make notifications for each user
                $new_releases = $albums->take($new_album_count);
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

            // Always update the artist with the new artist_album_count and artist_last_album_id, in case some albums were removed
            $followed_artist->artist_album_count = $albums->count();
            $followed_artist->artist_last_album_id = $albums->first()['album_id'];
            $followed_artist->save();
        }
    }
}
