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

    protected $hidden = [
        'spotify_access_token',
        'spotify_access_token_expiration',
        'spotify_refresh_token',
        'spotify_refresh_token_expiration',
    ];

    public function isTokenExpired()
    {
        return $this->getOriginal('spotify_access_token_expiration')->lessThan(now());
    }

    public function followed_artists()
    {
        return $this->belongsToMany(FollowedArtist::class, 'followed_artist_user', 'user_id', 'artist_id')->withTimestamps();
    }

    public function scopeDiscordUser($query)
    {
        return $query->whereNotNull('discord_username');
    }

    public function discord_notifications()
    {
        return $this->hasMany(Notification::class, 'user_id_to', 'user_id')
            ->unsent();
    }
}
