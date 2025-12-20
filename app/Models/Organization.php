<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organization extends Model
{
    use HasFactory; // Это нужно, чтобы работали фабрики (создание тестовых данных)

        protected $fillable = [
            'user_id',
            'name',
            'inn',
            'kpp',
            'type',
            'ogrn',
            'address',
            'is_deleted'
        ];
}
