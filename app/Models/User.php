<?php

/**
 * Название: User (Модель пользователя)
 * Дата-время: 20-12-2025 23:55
 * Описание: Основная модель аутентификации. Работает с таблицей b2b_users.
 * Включает связь с профилем и настройки безопасности.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Имя таблицы в БД
     */
    protected $table = 'b2b_users';

    /**
     * Атрибуты для массового заполнения.
     * Добавил поля из твоего дампа: phone, role, status.
     */
    protected $fillable = [
        'email',
        'phone',
        'password', // В Laravel это поле для хеша, по умолчанию 'password'
        'role',
        'status',
        'selected_org_id',
    ];

    /**
     * Скрытые поля (не выводятся в массивы/JSON)
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Типизация атрибутов
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // Laravel автоматически хеширует пароль при сохранении
        ];
    }

    /**
     * Название: profile
     * Описание: Связь с профилем пользователя.
     * Используется в Middleware для проверки заполненности данных.
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }
}