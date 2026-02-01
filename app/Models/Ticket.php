<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'b2b_requests';

    /**
     * Use request_code as the route key for URL binding
     */
    public function getRouteKeyName(): string
    {
        return 'request_code';
    }

    public const CATEGORIES = [
        'order'   => 'Вопрос по заказу',
        'finance' => 'Бухгалтерия / Документы',
        'product' => 'Вопрос по товару',
        'claim'   => 'Претензия / Возврат',
        'general' => 'Общий вопрос',
        'other'   => 'Прочее',
    ];
    
    public const STATUSES = [
        'new' => 'Новое',
        'in_progress' => 'В работе',
        'waiting_reply' => 'Ожидает ответа',
        'closed' => 'Закрыто',
    ];

    protected $fillable = [
        'user_id', 'admin_id', 'org_id', 'org_name', 'org_inn', 'org_kpp', 'org_ogrn',
        'user_email', 'user_phone', 'category', 'topic', 'status', 'request_code',
        'last_reply_at', 'last_reply_by'
    ];
    
    protected $casts = [
        'last_reply_at' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(TicketMessage::class, 'request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
    
    /**
     * Scope для новых обращений
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }
    
    /**
     * Scope для обращений в работе
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }
    
    /**
     * Scope для обращений с непрочитанными ответами
     */
    public function scopeUnreadByAdmin($query)
    {
        return $query->where('last_reply_by', 'user')
                     ->whereIn('status', ['new', 'in_progress', 'waiting_reply']);
    }

    
    /**
     * Получить читаемое название категории
     */
    public function getCategoryLabelAttribute()
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
    
    /**
     * Получить читаемое название статуса
     */
    public function getStatusLabelAttribute()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
