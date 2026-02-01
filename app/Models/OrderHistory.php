<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    use HasFactory;

    protected $table = 'b2b_order_history';

    public $timestamps = true;

    protected $fillable = [
        'order_id',
        'status_from_id',
        'status_to_id',
        'changed_by_id',
        'created_by',
        'event_type',
        'message',
        'status_from', // Legacy
        'status_to',   // Legacy
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Связь с заказом
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
