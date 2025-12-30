<?php

/**
 * Название: Discount.php
 * Дата-время: 28-12-2025 19:00
 * Описание: Модель персональных скидок.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $table = 'b2b_discounts';
    protected $guarded = ['id'];
}