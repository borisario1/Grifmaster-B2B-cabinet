<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'b2b_requests';

    public const CATEGORIES = [
        'order'   => 'Вопрос по заказу',
        'finance' => 'Бухгалтерия / Документы',
        'product' => 'Вопрос по товару',
        'claim'   => 'Претензия / Возврат',
        'general' => 'Общий вопрос',
        'other'   => 'Прочее',
    ];

    protected $fillable = [
        'user_id', 'org_id', 'org_name', 'org_inn', 'org_kpp', 'org_ogrn',
        'user_email', 'user_phone', 'category', 'topic', 'status', 'request_code'
    ];

    public function messages()
    {
        return $this->hasMany(TicketMessage::class, 'request_id');
    }

    /**
     * Получить читаемое название категории
     */
    public function getCategoryLabelAttribute()
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
