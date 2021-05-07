<?php


namespace App\Library;


class SpotifyLibrary
{
    public static function formatAlbumDate($album): string
    {
        $release_date = $album['album_release_date'];
        if ($album['album_release_date_precision'] == 'month')
        {
            $release_date = $release_date . "-01";
        }
        elseif ($album['album_release_date_precision'] == 'year')
        {
            $release_date = $release_date . "-01-01";
        }

        return $release_date;
    }
}
