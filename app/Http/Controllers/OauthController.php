<?php


namespace App\Http\Controllers;

use App\Http\Services\SpotifyService;
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

}
