<?php

return [
    // Настройки модального окна "Быстрый просмотр"
    'quick_view' => [
        // Показывать ли блок с описанием
        'show_summary' => true,
        
        // Показывать ли рейтинг и отзывы
        'show_rating' => true,

        // Приоритет изображений: 'webasyst' (с сайта) или '1c' (из файла)
        'image_priority' => 'webasyst', 
        
        // Желаемый размер изображения от Webasyst (заменяет дефолтный 970 или 750)
        // Варианты: '970', '2000' (макс), 'original' (если доступно)
        'webasyst_image_size' => '2000',

        // Базовый URL для картинок (на случай, если API вернет относительные пути)
        'media_url' => 'https://grifmaster.ru',
        
        // Какие характеристики выводить (по кодам из Webasyst)
        // Если будет [] - значит выводить всё
        'allowed_features' => [
            // --- БЛОК 1: ОСНОВНОЕ ---
            'brend',                    // Бренд (картинка)
            'kollektsiya',              // Коллекция
            'strana',                   // Страна изготовитель (картинка)
            'garantiya',                // Гарантия

            // --- БЛОК 2: ТИП И НАЗНАЧЕНИЕ ---
            'tip_tovara',               // Тип товара
            'naznachenie',              // Назначение
            'obshchee_naznachenie',     // Общее назначение
            
            // --- БЛОК 3: ВНЕШНИЙ ВИД И МАТЕРИАЛЫ ---
            'tsvet_izdeliya',           // Цвет изделия
            'material_shtangi',         // Материал штанги
            'material_leyki',           // Материал верхнего душа
            'gibkiy_shlang',            // Гибкий шланг (материал/длина)

            // --- БЛОК 4: ТЕХНИЧЕСКИЕ ДЕТАЛИ ---
            'vysota_shtangi',           // Высота штанги
            'diametr_leyki',            // Диаметр лейки
            'kolichestvo_rezhimov_leyki', // Количество режимов лейки

            // --- БЛОК 5: ЛОГИСТИКА И УПАКОВКА ---
            'length',                   // Длина упаковки
            'shirina',                  // Ширина упаковки
            'vysota_sm',                // Высота упаковки
            'weight',                   // Вес
            'obem_m3',                  // Объем, м3
            'material_upakovki',        // Материал упаковки
            'shtrikhkod',               // Штрихкод

            // --- БЛОК 6: СКРЫТЫЕ / ДУБЛИ (Оставляем выключенными) ---
            // 'tipy_tovarov',             // Типы товаров (дубль tip_tovara)
            // 'brend_tekst',              // Название бренда текст (есть картинка)
            // 'strana_proizvoditel',      // Страна текст (есть картинка)
            // 'nomenklaturnyy_nomer_1s',  // Номенклатурный номер 1С
            // '1c_status_gusev',          // Статус в 1С
            // 'vremenno_vyklyuchen',      // Доступен для продажи
            // 'f_status',                 // Статус товара
            // 'novogodnyaya_aktsiya',     // Акция
            // 'f_upselling',              // Upselling
            // 'content',                  // Content
            // 'brend_title',              // Заголовок бренда
        ],

        // Заголовки для характеристик (переименование)
        'feature_labels' => [
            // Основное
            'brend' => 'Бренд',
            'kollektsiya' => 'Коллекция',
            'strana' => 'Страна производства',
            'garantiya' => 'Гарантия',
            
            // Тип
            'tip_tovara' => 'Тип товара',
            'naznachenie' => 'Назначение',
            'obshchee_naznachenie' => 'Область применения',
            'tipy_tovarov' => 'Тип',

            // Внешний вид
            'tsvet_izdeliya' => 'Цвет',
            'material_shtangi' => 'Материал штанги',
            'material_leyki' => 'Материал лейки',
            'gibkiy_shlang' => 'Шланг',

            // Технические
            'vysota_shtangi' => 'Высота штанги',
            'diametr_leyki' => 'Диаметр лейки',
            'kolichestvo_rezhimov_leyki' => 'Режимы лейки',

            // Логистика
            'weight' => 'Вес (кг)',
            'length' => 'Длина упаковки (мм)',
            'shirina' => 'Ширина упаковки (мм)',
            'vysota_sm' => 'Высота упаковки (мм)',
            'obem_m3' => 'Объем (м³)',
            'material_upakovki' => 'Упаковка',
            'shtrikhkod' => 'Штрихкод (EAN)',
            'nomenklaturnyy_nomer_1s' => 'Код 1С',
            
            // Прочее
            'strana_proizvoditel' => 'Страна',
            'brend_tekst' => 'Бренд',
            'f_status' => 'Статус',
            'brend_title' => 'Бренд',
            'content' => 'Инфо',
            'novogodnyaya_aktsiya' => 'Акция',
            '1c_status_gusev' => 'Статус 1С',
            'f_upselling' => 'Сопутствующие товары',
            'vremenno_vyklyuchen' => 'Доступность',
        ],
    ],
];