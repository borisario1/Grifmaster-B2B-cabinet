<?php

/**
 * Название: config/b2b.php
 * Дата-время: 21-12-2025 19:30
 * Описание: Глобальные настройки системы B2B. 
 * Используется для управления версиями, контактами поддержки и API.
 */

return [
    // Основные параметры приложения
    'app_name' => 'Партнёрская территория GRIFMASTER',
    'version'  => '2.00.27',
    'updated'  => '28.12.2025',

    // Контакты поддержки (выводятся в футере)
    'support' => [
        'name'  => 'Борис Гусев',
        //'email' => '<a target="_blank" href="mailto:232@grifmaster.ru">232@grifmaster.ru</a>',
        'email' => '232@grifmaster.ru',
    ],

    // Интеграция с Webasyst (Заказы, Склад)
    'webasyst' => [
        'url' => env('WA_API_URL', 'https://grifmaster.ru/api.php'),
        'key' => env('WA_API_KEY', '7b27382d8e61fc3654fa816b26c257a7'),
    ],

    // Интеграция с DaData (Проверка ИНН, адресов)
    'dadata' => [
        'key'    => env('DADATA_API_KEY', '667530abf26b739c1121e410414a045a1a2e649e'),
        'secret' => env('DADATA_SECRET_KEY', 'c7f9b624ef145403ad4e0889e5b3a80728a1331b'),
    ],

    // Настройки интерфейса
    'branding' => [
        'logo_path' => 'img/Logo_GRIFMASTER-03.png',
        'fav_icon'  => 'https://qr.grifmaster.ru/uploads/img/logo_circle.jpg',
    ],
];