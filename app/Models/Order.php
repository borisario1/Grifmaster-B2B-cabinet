<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'b2b_orders';

    /**
     * Use order_code as the route key for URL binding
     */
    public function getRouteKeyName(): string
    {
        return 'order_code';
    }

    protected $fillable = [
        'order_code',
        'user_id',
        'org_id',
        'org_name',
        'org_inn',
        'org_kpp',
        'org_ogrn',
        'total_items',
        'total_amount',
        'currency',
        'status',
        'status_id',
        'admin_id',
        'comment',
        'closure_comment',
        'closed_at',
        'last_status_change_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'closed_at' => 'datetime',
        'last_status_change_at' => 'datetime',
    ];

    /**
     * Связь с пользователем
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Статус заказа
     */
    public function orderStatus()
    {
        return $this->belongsTo(OrderStatus::class, 'status_id');
    }

    /**
     * Ответственный менеджер
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Связь с позициями заказа
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Связь с историей заказа
     */
    public function history()
    {
        return $this->hasMany(OrderHistory::class, 'order_id');
    }

    /**
     * Scope для фильтрации по статусу
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope для новых заказов
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }
}
