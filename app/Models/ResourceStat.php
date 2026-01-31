<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceStat extends Model
{
    protected $table = 'b2b_resource_stats';
    
    public $timestamps = false;
    
    protected $fillable = [
        'resource_id',
        'user_id',
        'ip_address',
        'downloaded_at'
    ];
    
    protected $casts = [
        'downloaded_at' => 'datetime',
    ];
    
    /**
     * Get the resource that owns the stat.
     */
    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
    
    /**
     * Get the user that downloaded the resource.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
