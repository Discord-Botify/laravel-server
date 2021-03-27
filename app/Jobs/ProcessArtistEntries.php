<?php

namespace App\Jobs;

use App\Http\Services\SpotifyService;
use App\Models\FollowedArtist;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ProcessArtistEntries implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $artists;
    private $user_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Collection $artists, string $user_id)
    {
        $this->artists = $artists;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $spotify_service = new SpotifyService();
        $spotify_service->loadClientCredentials();
        foreach ($this->artists as $artist)
        {
            // If this artist already exists in the DB, skip it so we don't override the job which creates the notification
            $is_artist_in_db = filled(FollowedArtist::find($artist['artist_id']));
            if ($is_artist_in_db) continue;

            // Find the number of albums and the most recent release for this artist
            $albums = $spotify_service->getArtistsAlbums($artist['artist_id']);
            $number_of_albums = $albums->count();
            $most_recent_album = $albums->first();

            // Save the artist in the DB
            $artist['artist_album_count'] = $number_of_albums;
            $artist['artist_last_album_id'] = $most_recent_album['album_id'];
            $release_date = $most_recent_album['album_release_date'];
            if ($most_recent_album['album_release_date_precision'] == 'month')
            {
                $release_date = $release_date . "-01";
            }
            elseif ($most_recent_album['album_release_date_precision'] == 'year')
            {
                $release_date = $release_date . "-01-01";
            }
            $artist['artist_last_album_date'] = $release_date;

            FollowedArtist::create($artist);
        }

    }
}
