<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowedArtist extends Model
{
    protected $primaryKey = 'artist_id';
    public $incrementing = false;
    protected $guarded = [];

    public function users()
    {
        return $this->belongsToMany(User::class, 'followed_artist_user', 'artist_id', 'user_id')->withTimestamps();
    }
}
