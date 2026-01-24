<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotificationPref extends Model
{
    protected $table = 'b2b_user_notification_prefs';

    protected $fillable = [
        'user_id', 'notify_orders', 'notify_ticket', 'notify_news', 'notify_general'
    ];
}
