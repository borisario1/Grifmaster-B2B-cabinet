<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Brand extends Model
{
    protected $table = 'b2b_brands';
    
    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'origin_country',
        'production_country',
        'priority',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($brand) {
            if (empty($brand->slug)) {
                $brand->slug = Str::slug($brand->name);
            }
        });
    }
    
    /**
     * Get the resources for the brand.
     */
    public function resources()
    {
        return $this->hasMany(Resource::class);
    }
    
    /**
     * Scope a query to only include active brands.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope a query to order brands by priority and name.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'desc')->orderBy('name');
    }
}
