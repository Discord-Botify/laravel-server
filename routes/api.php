<?php

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
});

Route::middleware('session.auth')->group(function()
{
    Route::get('followed-artist/spotify', [SpotifyController::class, 'getFollowedArtistsFromSpotify'])->name('followed-artist-spotify');
    Route::get('followed-artist', [SpotifyController::class, 'getFollowedArtsitsFromDB'])->name('followed-artist-db');
});

Route::get('/test', function ()
{
    return 'Hello Botify Laravel!';
});

