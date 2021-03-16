<?php


namespace App\Http\Controllers;

use App\Http\Services\SpotifyService;
use App\Library\OauthLibrary;
use App\Models\AppSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cookie;

class OauthController extends BaseController
{
    public function signinPage(Request $request)
    {
        $spotify_service = new SpotifyService();
        $auth_link = $spotify_service->getAuthLink();
        return view('auth-test')->with(compact('auth_link'));
    }

    public function registerUser(Request $request)
    {
        $code = $request->get('code');

        // Get credentials for this user
        $spotify_service = new SpotifyService();
        $access_token = $spotify_service->getAccessToken($code);
        $refresh_token = $spotify_service->getRefreshToken();

        // Get this user's info
        $user_info = $spotify_service->getUserDetails();

        // Check if this user's email already exists in the database. If not, create them
        $email = $user_info->email;
        $user = User::updateOrCreate(
            [
                'email' => $email
            ],
            [
                'spotify_access_token' => $access_token,
                'spotify_access_token_expiration' => now()->addHour(),
                'spotify_refresh_token' => $refresh_token,
                'spotify_refresh_token_expiration' => now()->addYear(),
                'spotify_user_name' => $user_info->display_name,
                'spotify_id' => $user_info->id,
            ]
        );

        $session = AppSession::startSession($user->user_id);
        Cookie::queue(config('custom.cookie_session_token'), $session->app_session_id);

        return redirect(route('home'));
    }

    public function registerDiscordUser(Request $request)
    {
        $code = $request->get('code');

        // Exchange code for token
        // Exchange the auth code for a token
        $token_response = OauthLibrary::apiRequest('https://discord.com/api/oauth2/token', array(
            "grant_type" => "authorization_code",
            'client_id' => env('DISCORD_CLIENT_ID'),
            'client_secret' => env('DISCORD_CLIENT_SECRET'),
            'redirect_uri' => env('DISCORD_REDIRECT_URI'),
            'code' => $code
        ));
        $token = $token_response['access_token'];

        // Get the Discord user's ID
        $id_response = OauthLibrary::apiRequest('https://discord.com/api/users/@me',
            false,
            [
                'Authorization: Bearer ' . $token
            ]
        );

        // Save it to this user in the DB
        $user = AppSession::user();
        $user->discord_id = $id_response['id'];
        $user->discord_username = $id_response['username'];
        $user->save();

        return redirect(route('settings'));
    }

    public static function apiRequest($url, $post=FALSE, $headers=array()) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $response = curl_exec($ch);

        if($post)
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

        $headers[] = 'Accept: application/json';

//        if(session('access_token'))
//            $headers[] = 'Authorization: Bearer ' . session('access_token');

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

//        dd($ch);

        $response = curl_exec($ch);
        return json_decode($response, true);
    }
}
