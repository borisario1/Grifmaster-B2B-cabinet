<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDetail extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'b2b_product_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'summary',
        'description',
        'features',
        'images',
        'last_enriched_at',
        'likes_count',
        'views_count',
        'add_to_cart_count',
        'orders_count',
        'wishlist_count',
        'last_viewed_at',
        'rating',
        'rating_count',
        'url_slug',
        'documents',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;


    /**
     * Get the product that owns the details.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
