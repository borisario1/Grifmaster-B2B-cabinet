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
    'catalog' => [
        'title'   => 'Каталог',
        'desc'    => 'Актуальный список товаров в наличии',
        'icon'    => 'bi-grid-3x3-gap-fill',
        'url'     => '/partners-area/store',
        'group'   => 'orders',
        'show_in' => ['dashboard', 'burger', 'toolbar'],
    ],

    'cart' => [
        'title'   => 'Корзина',
        'desc'    => 'Товары, готовые к заказу',
        'icon'    => 'bi-basket2-fill',
        'url'     => '/partners-area/store/cart',
        'group'   => 'orders',
        'show_in' => ['dashboard', 'burger'],
    ],

    'orders' => [
        'title'   => 'Заказы',
        'desc'    => 'История и статус заказов',
        'icon'    => 'bi-receipt-cutoff',
        'url'     => '/partners-area/orders',
        'group'   => 'orders',
        'show_in' => ['dashboard', 'burger', 'toolbar'],
    ],


    // =============================
    // БЛОК: БИЗНЕС, ОРГАНИЗАЦИИ И ДОКУМЕНТЫ
    // =============================
    'organizations' => [
        'title'   => 'Организации',
        'desc'    => 'ИНН, реквизиты, юр. данные',
        'icon'    => 'bi-building',
        'url'     => '/partners-area/organizations',
        'group'   => 'business',
        'show_in' => ['dashboard', 'burger', 'toolbar'],
    ],

    'documents' => [
        'title'   => 'Документы',
        'desc'    => 'Договора, счета, акты',
        'icon'    => 'bi-folder2-open',
        'url'     => '/partners-area/documents',
        'group'   => 'business',
        'show_in' => ['dashboard', 'burger', 'toolbar'],
    ],

    'prices' => [
        'title'   => 'Прайс-листы',
        'desc'    => 'Актуальные выгрузки',
        'icon'    => 'bi-file-spreadsheet',
        'url'     => '/partners-area/prices',
        'group'   => 'business',
        'show_in' => ['dashboard', 'burger', 'toolbar'],
    ],

    'files' => [
        'title'   => 'Файлы',
        'desc'    => 'Материалы и ресурсы',
        'icon'    => 'bi-cloud-download',
        'url'     => '/partners-area/files',
        'group'   => 'business',
        'show_in' => ['dashboard', 'burger', 'toolbar'],
    ],

    'api' => [
        'title'   => 'API-сервис',
        'desc'    => 'Интеграции с 1С, CRM и др.',
        'icon'    => 'bi-code-slash',
        'url'     => '/partners-area/api',
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
        'url'     => '/partners-area/profile',
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
        'url'     => '/partners-area/notifications',
        'group'   => 'settings',
        'show_in' => ['dashboard', 'burger'],
    ],

    'requests' => [
        'title'   => 'Обращения',
        'desc'    => 'Ваши заявки и ответы сотрудников',
        'icon'    => 'bi-life-preserver',
        'url'     => '/partners-area/requests',
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
        'url'     => '/partners-area/logout',
        'group'   => 'settings',
        'show_in' => ['dashboard', 'burger'],
    ],

];
