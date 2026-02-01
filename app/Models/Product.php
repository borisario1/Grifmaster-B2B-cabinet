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
    protected $fillable = [
        'code_1c',
        'name',
        'free_stock',
        'article',
        'brand',
        'price',
        'min_quantity',
        'status',
        'is_featured',
        'sort_order',
        'product_type',
        'product_category',
        'collection',
        'image_filename',
        'last_synced_at',
        'is_active',
    ];

    /**
     * Аксессор для получения URL изображения.
     * Если файла нет — возвращаем заглушку.
     */
    public function getImageUrlAttribute(): string
    {
        // Получаем имя файла из БД
        $filename = $this->image_filename;

        // Если файла нет или поле пустое — возвращаем заглушку из конфига
        if (empty($filename)) {
            return config('b2b.1c_csv_price.csv_noimage');
        }

        // Берем базовый путь и склеиваем с именем файла
        // config(...) вернет строку типа 'https://.../images/'
        return config('b2b.1c_csv_price.csv_images') . $filename;
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

    /**
     * Get the details for the product.
     */
    public function details()
    {
        return $this->hasOne(ProductDetail::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Явно указываем типы
     */
    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
            'price' => 'decimal:2',
            'free_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
    * Как только товар прилетает из 1С 
    * (создается или обновляется), у него автоматически 
    * появляется запись в таблице маркетинга, если её ещё нет. 
    */
    protected static function booted()
    {
        static::saved(function ($product) {
            // Гарантируем наличие записи в деталях для хранения лайков/просмотров
            $product->details()->firstOrCreate([]);
        });
    }
}