<?php


namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;


class WebpageController extends BaseController
{
    public function home()
    {
        return view('home');
    }

    public function settings()
    {
        $discord_redirect_uri = env('DISCORD_REDIRECT_URI');
        $discord_client_id = env('DISCORD_CLIENT_ID');
        $discord_oauth_url = "https://discord.com/api/oauth2/authorize?client_id=$discord_client_id&redirect_uri=$discord_redirect_uri&response_type=code&scope=identify";

        return view('settings', compact('discord_oauth_url'));
    }
}
