<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class AppSession extends Model
{
    // Settings to make the primary key a UUIDs
    protected $primaryKey = 'app_session_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = ['app_session_id'];

    // Instance of the currently authenticated user
    private static $user_id = null;

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->setAttribute($model->getKeyName(), Uuid::uuid4());
        });
    }

    public static function getSession(string $session_id)
    {
        return AppSession::where('app_session_id', $session_id)->active()->first();
    }

    public static function startSession(string $user_id): AppSession
    {
        self::authenticate($user_id);
        return AppSession::create([
            'user_id' => $user_id,
            'app_session_expiration' => now()->addYear()
        ]);

    }

    public function scopeActive($query)
    {
        return $query->where('app_session_expiration', '>', now());
    }

    public static function authenticate(string $user_id)
    {
        self::$user_id = $user_id;
    }

    public static function unauthenticate()
    {
        self::$user_id = null;
    }

    public static function getLoggedInUserId()
    {
        return self::$user_id;
    }

}
