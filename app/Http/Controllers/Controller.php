<?php

namespace App\Http\Controllers;

use App\Console\Commands\QueueNotifications;
use App\Http\Services\SpotifyService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cookie;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function test()
    {
        $service = new SpotifyService();
        $service->loadClientCredentials();

        dd($service->getArtistsAlbums(config('custom.death_grips_id')));
//
//        $art = new QueueNotifications();
//        $art->handle();
//        Cookie::queue('test', 'test');
        return 'Test!!';
    }
}
