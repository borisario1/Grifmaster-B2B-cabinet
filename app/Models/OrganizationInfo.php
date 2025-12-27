<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationInfo extends Model
{
    use SoftDeletes;

    protected $table = 'b2b_organization_infos';
    protected $fillable = [
        'organization_id', 'dadata_raw', 'status', 'branch_type', 
        'name_full', 'registered_at', 'address'
    ];

    // Указываем, что dadata_raw — это массив/объект (JSON)
    protected $casts = [
        'dadata_raw' => 'array',
        'registered_at' => 'datetime',
    ];

    // Обратная связь
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }
}