<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationInfo extends Model
{
    protected $fillable = [
        'organization_id', 'dadata_raw', 'status', 'branch_type', 
        'name_full', 'registered_at', 'address'
    ];

    // Указываем, что dadata_raw — это массив/объект (JSON)
    protected $casts = [
        'dadata_raw' => 'array',
        'registered_at' => 'datetime',
    ];
}
