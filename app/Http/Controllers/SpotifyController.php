<?php


namespace App\Http\Controllers;

use App\Http\Services\SpotifyService;
use Illuminate\Routing\Controller as BaseController;

class SpotifyController extends BaseController
{

    public function getFollowedArtistsFromSpotify()
    {
        $service = new SpotifyService();
        $service->loadUser();

        return $service->getUserFollowedArtistsFromSpotify();
    }

    public function getFollowedArtsitsFromDB()
    {

    }

}
