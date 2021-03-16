<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public $primaryKey = 'notification_id';
    public $incrementing = false;
    protected $guarded = [];

    public function scopeUnDismissed($query)
    {
        return $query->where('notification_sent', 1)
            ->where('notification_dismissed', 0);
    }

    public function scopeUnsent($query)
    {
        return $query->where('notification_sent', 0);
    }
}
