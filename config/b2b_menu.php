<?php
/**
 * b2b_menu.php
 * Единая конфигурация меню B2B.
 *
 * Каждый пункт может отображаться в:
 * - dashboard
 * - burger
 * - toolbar
 * - ...
 */


return [

    // =============================
    // БЛОК: ЗАКАЗЫ И КАТАЛОГ
    // =============================
    'homepage' => [
        //'title'   => 'В начало',
        'desc'    => 'Главная страница приложения',
        'icon'    => 'bi-house-door-fill',
        'url'     => '/dashboard',
        'group'   => 'orders',
        //'priority' => 'high', // Высокий приоритет - отображается первым в бургер меню
        'show_in' => ['toolbar'],
    ],

    'catalog' => [
        'title'   => 'Каталог',
        'desc'    => 'Актуальный список товаров в наличии',
        'icon'    => 'bi-grid-3x3-gap-fill',
        'url'     => '/store',
        'group'   => 'orders',
        'priority' => 'high', // Высокий приоритет - отображается первым в бургер меню
        'show_in' => ['dashboard', 'burger', 'toolbar'],
    ],

    'cart' => [
        'title'   => 'Корзина',
        'desc'    => 'Товары, готовые к заказу',
        'icon'    => 'bi-basket2-fill',
        'url'     => '/store/cart',
        'group'   => 'orders',
        'priority' => 'high', // Высокий приоритет - отображается первым в бургер меню
        'show_in' => ['dashboard', 'burger'],
    ],
    
    'wishlist' => [
        'title'   => 'Избранное',
        'desc'    => 'Отложенные вами товары, которые понравились',
        'icon'    => 'bi-heart',
        'url'     => '/store/wishlist',
        'group'   => 'orders',
        'show_in' => ['dashboard', 'burger'],
    ],

    'orders' => [
        'title'   => 'Заказы',
        'desc'    => 'История и статус заказов',
        'icon'    => 'bi-receipt-cutoff',
        'url'     => '/store/orders',
        'group'   => 'orders',
        'priority' => 'high', // Высокий приоритет - отображается первым в бургер меню
        'show_in' => ['dashboard', 'burger', 'toolbar'],
    ],


    // =============================
    // БЛОК: БИЗНЕС, ОРГАНИЗАЦИИ И ДОКУМЕНТЫ
    // =============================
    'organizations' => [
        'title'   => 'Организации',
        'desc'    => 'ИНН, реквизиты, юр. данные',
        'icon'    => 'bi-building',
        'url'     => '/organizations',
        'group'   => 'business',
        'show_in' => ['dashboard', 'burger', 'toolbar'],
    ],

    'documents' => [
        'title'   => 'Документы',
        'desc'    => 'Договора, счета, акты',
        'icon'    => 'bi-folder2-open',
        'url'     => '/documents',
        'group'   => 'business',
        'show_in' => ['dashboard', 'burger', 'toolbar'],
    ],

    'prices' => [
        'title'   => 'Прайс-листы',
        'desc'    => 'Актуальные выгрузки',
        'icon'    => 'bi-file-spreadsheet',
        'url'     => '/prices',
        'group'   => 'business',
        'show_in' => ['dashboard', 'burger', 'toolbar'],
    ],

    'files' => [
        'title'   => 'Файлы',
        'desc'    => 'Материалы и ресурсы',
        'icon'    => 'bi-cloud-download',
        'url'     => '/files',
        'group'   => 'business',
        'show_in' => ['dashboard', 'burger', 'toolbar'],
    ],

    'api' => [
        'title'   => 'API-сервис',
        'desc'    => 'Интеграции с 1С, CRM и др.',
        'icon'    => 'bi-code-slash',
        'url'     => '/api',
        'group'   => 'business',
        'show_in' => ['dashboard', 'burger'],
    ],

    // =============================
    // БЛОК: НАСТРОЙКИ И СЕРВИСЫ
    // =============================
    'profile' => [
        'title'   => 'Мой профиль',
        'desc'    => 'Контакты и данные сотрудника',
        'icon'    => 'bi-person-circle',
        'url'     => '/profile',
        'group'   => 'settings',
        'show_in' => ['dashboard', 'burger'],
    ],

    // =============================
    // БЛОК: УВЕДОМЛЕНИЯ И ОБРАЩЕНИЯ
    // =============================
    'notifications' => [
        'title'   => 'Уведомления',
        'desc'    => 'Системные события, новости и изменения',
        'icon'    => 'bi-bell',
        'url'     => '/notifications',
        'group'   => 'settings',
        'show_in' => ['dashboard', 'burger'],
    ],

    'requests' => [
        'title'   => 'Обращения',
        'desc'    => 'Ваши заявки и ответы сотрудников',
        'icon'    => 'bi-life-preserver',
        'url'     => '/requests',
        'group'   => 'settings',
        'show_in' => ['dashboard', 'burger', 'toolbar'],
    ],

    // =============================
    // ВЫХОД ИЗ СИСТЕМЫ
    // =============================
    'logout' => [
        'title'   => 'Выйти',
        'desc'    => 'Завершить работу',
        'icon'    => 'bi-box-arrow-right',
        'url'     => '/logout',
        'group'   => 'settings',
        'show_in' => ['dashboard', 'burger'],
    ],

    // =============================
    // НАСТРОЙКИ МОБИЛЬНОГО БУРГЕР МЕНЮ
    // =============================
    'burger_settings' => [
        'show_user_block'   => true,   // Инфо о пользователе сверху
        'show_priority_block' => true,   // Основные пункты меню
        'show_contact_info' => true,   // Контакты менеджера снизу
        'manager_info'     => 'пн–пт 9:00–18:00',
        'manager_phone'     => '+7 499 346-75-67',
        'manager_email'     => 'sale@grifmaster.ru',
    ],

    // Кнопки для нижнего Tab Bar (фиксированные снизу экрана)
    'mobile_tab_bar' => [
        'enabled' => true,
        'items' => [
            ['title' => 'Главная', 'icon' => 'bi-house', 'url' => '/dashboard'],
            ['title' => 'Каталог', 'icon' => 'bi-grid',  'url' => '/store'],
            ['title' => 'Корзина', 'icon' => 'bi-basket', 'url' => '/store/cart', 'is_cart' => true],
            ['title' => 'Заказы',  'icon' => 'bi-box-seam', 'url' => '/store/orders'],
            ['title' => 'Профиль', 'icon' => 'bi-person', 'url' => '/profile'],
        ]
    ]
];
