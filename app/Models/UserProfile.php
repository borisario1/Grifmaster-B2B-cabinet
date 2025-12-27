<?php

/**
 * Название: UserProfile (Модель профиля)
 * Дата-время: 21-12-2025 17:15
 * Описание: Связывает пользователя с его персональными данными. 
 * Указано явное имя таблицы из дампа.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfile extends Model
{
    use SoftDeletes;

    // Явно указываем таблицу
    protected $table = 'b2b_user_profile';
    // Указываем атрибуты таблицы
    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'middle_name', 
        'birth_date', 'full_name', 'job_title', 'work_phone', 'messenger',
        'notify_general', 'notify_news', 'notify_orders', 'notify_ticket'
    ];

    protected $casts = [
        // Это гарантирует, что при обращении $profile->notify_news мы получим boolean, а не 0 или 1
        'notify_general' => 'boolean',
        'notify_news'    => 'boolean',
        'notify_orders'  => 'boolean',
        'notify_ticket'  => 'boolean',
    ];
    
    /**
     * Обратная связь: профиль принадлежит пользователю
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}