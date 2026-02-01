<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Resource extends Model
{
    protected $table = 'b2b_resources';
    
    protected $fillable = [
        'title',
        'description',
        'type',
        'file_path',
        'brand_id',
        'is_active',
        'is_pinned',
        'require_confirmation',
        'confirmation_text',
        'confirm_btn_text',
        'external_link'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'is_pinned' => 'boolean',
        'require_confirmation' => 'boolean',
    ];
    
    /**
     * Get the brand that owns the resource.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    
    /**
     * Get the download statistics for the resource.
     */
    public function stats()
    {
        return $this->hasMany(ResourceStat::class);
    }
    
    /**
     * Scope a query to only include active resources.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope a query to only include pinned resources.
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }
    
    /**
     * Scope a query to filter by brand.
     */
    public function scopeByBrand($query, $brandId)
    {
        return $query->where('brand_id', $brandId);
    }
    
    /**
     * Scope a query to only include general (non-brand) resources.
     */
    public function scopeGeneral($query)
    {
        return $query->whereNull('brand_id');
    }
    
    /**
     * Get the file size in bytes.
     */
    public function getFileSize()
    {
        if ($this->external_link) {
            return null;
        }
        
        return Storage::disk('local')->exists($this->file_path) 
            ? Storage::disk('local')->size($this->file_path) 
            : 0;
    }
    
    /**
     * Get the total download count.
     */
    public function getDownloadCount()
    {
        return $this->stats()->count();
    }
    
    /**
     * Get the file extension.
     */
    public function getFileExtension()
    {
        return pathinfo($this->file_path, PATHINFO_EXTENSION);
    }
}
