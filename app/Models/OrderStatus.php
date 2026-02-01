<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    use HasFactory;

    protected $table = 'b2b_order_statuses';

    protected $fillable = [
        'name',
        'label',
        'color',
        'sort_order',
        'is_default',
        'is_final',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_default' => 'boolean',
        'is_final' => 'boolean',
    ];

    /**
     * Заказы с этим статусом
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'status_id');
    }

    /**
     * Получить статус по умолчанию
     */
    public static function getDefault()
    {
        return static::where('is_default', true)->first();
    }

    /**
     * Scope для статуса по умолчанию
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope для сортировки
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope для финальных статусов
     */
    public function scopeFinal($query)
    {
        return $query->where('is_final', true);
    }

    /**
     * Scope для активных статусов
     */
    public function scopeActive($query)
    {
        return $query->where('is_final', false);
    }
}
