<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes; // Это нужно, чтобы работали фабрики (создание тестовых данных)\
    protected $table = 'b2b_organizations';
        protected $fillable = [
            'user_id',
            'name',
            'inn',
            'kpp',
            'type',
            'ogrn',
            'address',
            //'is_deleted'
        ];

    // Связь: Организация принадлежит юзеру
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Связь: У организации есть 1 инфо (DaData)
    public function info()
    {
        return $this->hasOne(OrganizationInfo::class, 'organization_id');
    }
}