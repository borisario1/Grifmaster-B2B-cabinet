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
        'title_in_burger' => 'Каталог', // Как будет называться в бургер меню
        'desc'    => 'Актуальный список товаров в наличии, РРЦ и персональные скидки',
        'icon'    => 'bi-grid-3x3-gap-fill',
        'url'     => '/catalog',
        'group'   => 'orders',
        'priority' => 'high', // Высокий приоритет - отображается первым в бургер меню
        'show_in' => ['dashboard', 'burger', 'toolbar'],
        'image'   => 'img/menu/catalog.png', // Изображение или иконка для карточки
    ],

    'cart' => [
        'title'   => 'Корзина',
        'title_in_burger' => 'Корзина', // Как будет называться в бургер меню
        'desc'    => 'Товары, которые вы добавили в корзину. Отправьте заказ онлайн.',
        'icon'    => 'bi-basket2',
        'url'     => '/catalog/cart',
        'group'   => 'orders',
        'priority' => 'high', // Высокий приоритет - отображается первым в бургер меню
        'show_in' => ['dashboard', 'burger'],
        'image'   => 'img/menu/cart.png',
    ],
    
    'wishlist' => [
        'title'   => 'Избранное',
        'desc'    => 'Товары в избранном, которые вы отложили для будущих покупок.',
        'icon'    => 'bi-star',
        'url'     => '/catalog/wishlist',
        'group'   => 'orders',
        'show_in' => ['dashboard', 'burger'],
        'image'   => 'img/menu/wishlist.png',
    ],

    'orders' => [
        'title'   => 'Ваши заказы',
        'title_in_burger' => 'Заказы', // Как будет называться в бургер меню
        'desc'    => 'Все созданные вами заказы, их история, статусы доставок и состав.',
        'icon'    => 'bi-receipt-cutoff',
        'url'     => '/catalog/orders',
        'group'   => 'orders',
        'priority' => 'high', // Высокий приоритет - отображается первым в бургер меню
        'show_in' => ['dashboard', 'burger', 'toolbar'],
        'image'   => 'img/menu/orders.png',
    ],

    'liked' => [
        'title'   => 'Понравилось',
        'desc'    => 'Товары, которые понравились вам и другим пользователям',
        'icon'    => 'bi-heart',
        'url'     => '/catalog/liked',
        'group'   => 'orders',
        'show_in' => ['dashboard'],
        'image'   => 'img/menu/liked.png',
    ],

    'ordered' => [
        'title'   => 'Заказывали ранее',
        'title_in_burger' => 'Заказывали',
        'desc'    => 'Товары, которые вы заказывали. Повторите заказ, быстро создав его.',
        'icon'    => 'bi bi-bag-check',
        'url'     => '/catalog/ordered',
        'group'   => 'orders',
        'show_in' => ['dashboard'],
        'image'   => 'img/menu/ordered.png',
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
        'show_in' => ['dashboard', 'burger'],
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

    // =============================
    // СЛАЙДЕР БАННЕРОВ
    // =============================
    'banners' => [
        [
            'image'  => 'img/banners/1_1_2.jpg', // Путь к файлу в public/
            'url'    => '/catalog/sale', // Куда ведет клик
            'alt'    => 'Распродажа сезона',
            'target' => '_blank', // или '_blank'
        ],
        [
            'image'  => 'img/banners/1_3.jpg',
            'url'    => '/catalog/new',
            'alt'    => 'Новинки',
            'target' => '_self', // или '_blank'
        ],
        [
            'image'  => 'img/banners/urbatec_1_1_soon.jpg',
            'url'    => '/page/delivery',
            'alt'    => 'Бесплатная доставка',
            'target' => '_self', // или '_blank'

        ],
    ],

    // Кнопки для нижнего Tab Bar (фиксированные снизу экрана)
    'mobile_tab_bar' => [
        'enabled' => true,
        'items' => [
            ['title' => 'Главная', 'icon' => 'bi-house', 'url' => '/dashboard'],
            ['title' => 'Каталог', 'icon' => 'bi-grid',  'url' => '/catalog'],
            ['title' => 'Корзина', 'icon' => 'bi-basket', 'url' => '/catalog/cart', 'is_cart' => true],
            ['title' => 'Заказы',  'icon' => 'bi-box-seam', 'url' => '/catalog/orders'],
            ['title' => 'Профиль', 'icon' => 'bi-person', 'url' => '/profile'],
        ]
    ]
];
