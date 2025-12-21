<?php

/**
 * Название: UserProfile (Модель профиля)
 * Дата-время: 21-12-2025 17:15
 * Описание: Связывает пользователя с его персональными данными. 
 * Указано явное имя таблицы из дампа.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    // Явно указываем таблицу из твоего дампа
    protected $table = 'b2b_user_profile';

    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'middle_name', 
        'birth_date', 'full_name', 'job_title', 'work_phone', 'messenger'
    ];

    /**
     * Обратная связь: профиль принадлежит пользователю
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}