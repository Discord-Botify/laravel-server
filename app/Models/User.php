<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Model
{
//    use Notifiable;
    protected $dates = ['spotify_access_token_expiration', 'spotify_refresh_token_expiration'];
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'email',
        'spotify_access_token',
        'spotify_access_token_expiration',
        'spotify_refresh_token',
        'spotify_refresh_token_expiration',
        'spotify_user_name',
        'spotify_id',
    ];

    public function isTokenExpired()
    {
        return $this->getOriginal('spotify_access_token_expiration')->lessThan(now());
    }

    public function app_playlists()
    {
        return $this->hasMany(AppPlaylist::class);
    }


}
