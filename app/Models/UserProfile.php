<?php

/**
 * Название: UserProfile (Модель профиля)
 * Дата-время: 20-12-2025 22:15
 * Описание: Связывает пользователя с его персональными данными.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'middle_name', 
        'birth_date', 'full_name', 'job_title', 'work_phone', 'messenger'
    ];

    // Обратная связь: профиль принадлежит пользователю
    public function user() {
        return $this->belongsTo(User::class);
    }
}