<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public $primaryKey = 'notification_id';
    public $incrementing = false;
    protected $guarded = [];
}
