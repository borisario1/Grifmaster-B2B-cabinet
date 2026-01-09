<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $table = 'b2b_cart_items';

    protected $fillable = [
        'user_id',
        'org_id',
        'product_id',
        'qty',
    ];

    /**
     * Связь с товаром: получаем имя, артикул и цену
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Связь с организацией
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }
}