<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowedArtistUser extends Model
{
    protected $table = 'followed_artist_user';
    protected $primaryKey = null;

    protected $guarded = [];
}
