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
use Illuminate\Database\Eloquent\SoftDeletes;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Filament\Models\Contracts\HasName;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory, Notifiable, SoftDeletes;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin' || $this->role === 'manager';
    }

    public function getFilamentName(): string
    {
        return $this->profile?->first_name ? "{$this->profile->first_name} {$this->profile->last_name}" : $this->email;
    }

    public function getNameAttribute(): string
    {
        return $this->getFilamentName();
    }



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
        'password',
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
            'password' => 'hashed',
        ];
    }

    /**
     * Название: profile
     * Описание: Связь с профилем пользователя.
     * Используется в Middleware для проверки заполненности данных.
     */
    public function profile()
    {
        // withDefault создает пустой объект в памяти, если записи в БД нет.
        // Теперь $user->profile всегда будет объектом, а не null.
        return $this->hasOne(UserProfile::class, 'user_id')->withDefault([
            'last_name' => '',
            'first_name' => '',
        ]);
    }

    // Добавим связь: У юзера может быть много организаций
    public function organizations()
    {
        return $this->hasMany(Organization::class, 'user_id');
    }

    /**
     * Текущая выбранная организация пользователя.
     * Используем belongsTo, так как ID организации хранится в таблице users (поле selected_org_id)
     */
    public function currentOrganization()
    {
        return $this->belongsTo(Organization::class, 'selected_org_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}