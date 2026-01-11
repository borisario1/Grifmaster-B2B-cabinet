<?php

/**
 * Название: config/b2b.php
 * Дата-время: 21-12-2025 19:30
 * Описание: Глобальные настройки системы B2B. 
 * Используется для управления версиями, контактами поддержки и API.
 */

return [
    // Основные параметры приложения
    'app_name' => 'Бизнес-кабинет',
    'version'  => '2.21.a6f7c',
    'updated'  => '10.01.2026',

    // Контакты поддержки (выводятся в футере)
    'support' => [
        'name'  => 'Поддержка пользователей -',
        //'email' => '<a target="_blank" href="mailto:232@grifmaster.ru">232@grifmaster.ru</a>',
        'email' => '232@grifmaster.ru',
    ],

    // Доступ к CSV данным из 1С и файлам изображений для формирования каталога
    '1c_csv_price' => [
        'csv_source'    => env('1C_CSV_SOURCE', 'http://data.grifmaster.ru/files/dq9/data/products.csv'),
        'csv_images' => env('1C_CSV_IMAGES', 'https://data.grifmaster.ru/files/dq9/data/images/'),
        'csv_noimage'  => env('1C_CSV_NOIMAGE', 'https://data.grifmaster.ru/files/dq9/data/noimage.png'),
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
        'url_party'  => env('DADATA_URL_PARTY', 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party'),
    ],

    // Интеграция с SMTP.BZ (Рассылки)
    'smtpbz' => [
        'url'        => env('SMTPBZ_URL', 'https://api.smtp.bz/v1/smtp/send'),
        'key'        => env('SMTPBZ_API_KEY'),
        'from_email' => env('SMTPBZ_FROM_EMAIL', 'no-reply@grifmaster.ru'),
        'from_name'  => env('SMTPBZ_FROM_NAME', 'Grifmaster B2B'),
    ],

    // Настройки интерфейса
    'branding' => [
        'logo_path' => 'img/Logo_GRIFMASTER-03.png',
        'fav_icon'  => 'https://qr.grifmaster.ru/uploads/img/logo_circle.jpg',
    ],
];