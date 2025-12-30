<?php

/**
 * Название: Product.php
 * Дата-время: 28-12-2025 17:45
 * Описание: Модель товара. Включает логику получения изображений и расчета партнерских цен.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    /**
     * Указываем таблицу с префиксом
     */
    protected $table = 'b2b_products';

    /**
     * Разрешаем массовое заполнение всех полей (кроме ID)
     */
    protected $guarded = ['id'];

    /**
     * Константа для заглушки
     */
    const NO_IMAGE_URL = 'https://data.grifmaster.ru/files/dq9/data/noimage.png';

    /**
     * Аксессор для получения URL изображения.
     * Если файла нет — возвращаем заглушку.
     */
    public function getImageUrlAttribute(): string
    {
        if (empty($this->image_filename)) {
            return self::NO_IMAGE_URL;
        }

        // Базовый путь из твоего конфига
        return "https://data.grifmaster.ru/files/dq9/data/images/" . $this->image_filename;
    }

    /**
     * Расчет цены с учетом скидки.
     * В будущем здесь будет логика поиска скидки в b2b_discounts.
     * * @param int $discountPercent
     * @return float
     */
    public function getPartnerPrice(int $discountPercent = 0): float
    {
        if ($discountPercent <= 0) {
            return (float)$this->price;
        }

        return round($this->price * (100 - $discountPercent) / 100, 2);
    }
}