<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SpotifyController;
use App\Http\Controllers\OauthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::prefix('oauth/')->name('oauth.')->group(function()
{
    Route::get('spotify-redirect', [OauthController::class, 'registerUser'])->name('register-user');

    Route::get('discord-redirect', [OauthController::class, 'registerDiscordUser'])->name('register-discord-user')->middleware('session.auth');
});

Route::middleware('session.auth')->group(function()
{
    // Artist Management
    Route::get('followed-artist/spotify', [SpotifyController::class, 'getFollowedArtistsFromSpotify'])->name('followed-artist-spotify');
    Route::get('followed-artist', [SpotifyController::class, 'getFollowedArtistsFromDB'])->name('followed-artist-db');

    // Notifications
    Route::get('notification/un-dismissed', [NotificationController::class, 'unDismissed'])->name('notification-un-dismissed');
    Route::put('notification/{notification_id}', [NotificationController::class, 'dismiss'])->name('notification-dismiss');
    Route::put('notification', [NotificationController::class, 'dismissAll'])->name('notification-dismiss-all');
});

Route::get('/test', function ()
{
    $test = collect(['2021', '2020', '2020-01', '2020-03', '2020-02']);

    dd($test->sort());

    return 'Hello Botify Laravel!';
});

