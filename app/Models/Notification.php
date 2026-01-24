<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'b2b_notifications';

    protected $fillable = [
        'user_id', 'event_type', 'title', 'message', 'link_url', 'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];
}
