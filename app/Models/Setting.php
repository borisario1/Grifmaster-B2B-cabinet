<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'b2b_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Получить значение настройки по ключу
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        // Простой кэш в рамках запроса (static variable)
        static $cache = [];

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        try {
            $setting = self::where('key', $key)->first();
            $value = $setting ? $setting->value : $default;
        } catch (\Exception $e) {
            // Если таблицы нет или ошибка БД — возвращаем дефолт
            return $default;
        }

        $cache[$key] = $value;

        return $value;
    }

    /**
     * Установить значение настройки
     *
     * @param string $key
     * @param mixed $value
     * @param string $group
     * @return self
     */
    public static function set(string $key, $value, string $group = 'general')
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value, 
                'group' => $group
            ]
        );
    }
}
