<?php

/**
 * Название: ErrorPagesTest
 * Дата-время: 28-12-2025 10:30
 * Описание: Тестирование страниц ошибок.
 * * ЧТО ПРОВЕРЯЕМ:
 * 1. Физическое наличие шаблонов (404, 403, 500).
 * 2. ЛОГИКУ: Ссылка во второй кнопке ("В каталог") подтягивается из конфига, а не прибита гвоздями.
 * 3. КОНФИГУРАЦИЮ: В реальном конфиге b2b_menu.php действительно есть валидная секция для этой кнопки.
 */

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

uses(TestCase::class);

test('шаблоны ошибок (404, 403, 500) существуют и рендерятся', function () {
    $codes = ['404', '403', '500'];

    foreach ($codes as $code) {
        // Проверяем наличие файла
        expect(view()->exists("errors.$code"))
            ->toBeTrue("Файл шаблона errors/$code.blade.php не найден");

        // Проверяем рендеринг (что нет синтаксических ошибок Blade)
        $rendered = view("errors.$code")->render();
        expect($rendered)->toContain("Ошибка $code");
    }
});

test('шаблон берет ссылку и текст кнопки ДИНАМИЧЕСКИ из конфига', function () {
    // 1. Придумываем уникальные фейковые данные
    $fakePath  = '/super-unique-link-' . uniqid();
    $fakeTitle = 'Super Test Catalog ' . uniqid();
    $fakeIcon  = 'bi-radioactive-' . uniqid();

    // 2. Подменяем конфиг
    Config::set('b2b_menu.catalog', [
        'url'   => $fakePath,
        'title' => $fakeTitle,
        'icon'  => $fakeIcon,
        'show_in' => [],
        'group' => 'test_group'
    ]);

    // 3. Рендерим страницу (404 использует общий layout)
    $view = view('errors.404')->render();

    // 4. ПРОВЕРКА

    // А) Проверяем URL. 
    // Функция url() в Blade добавляет домен, поэтому ищем вхождение пути.
    // Если в конфиге '/foo', то в HTML будет 'http://localhost/foo'
    expect($view)->toContain($fakePath);

    // Б) Проверяем Заголовок.
    // В шаблоне стоит mb_strtolower(), поэтому мы ожидаем строку в нижнем регистре.
    // "Super Test Catalog" -> "super test catalog"
    expect($view)->toContain(mb_strtolower($fakeTitle));

    // В) Проверяем Иконку.
    expect($view)->toContain($fakeIcon);
});

test('конфиг файл b2b_menu содержит валидную ссылку для кнопки шаблона', function () {
    // ЭТО ТЕСТ РЕАЛЬНЫХ ДАННЫХ
    // Мы проверяем файл config/b2b_menu.php, чтобы убедиться, что мы не ссылаемся на пустоту.
    
    // 1. Берем реальный конфиг (без Mock)
    $realConfig = config('b2b_menu.catalog');

    // 2. Проверяем структуру
    expect($realConfig)
        ->not->toBeNull('В конфиге b2b_menu.php отсутствует ключ "catalog"')
        ->toBeArray('Секция catalog должна быть массивом');

    // 3. Проверяем обязательные поля
    expect($realConfig)->toHaveKeys(['url', 'title', 'icon']);

    // 4. Проверяем, что URL не пустой
    expect($realConfig['url'])
        ->not->toBeEmpty('URL каталога в конфиге пустой!')
        ->toBeString();
});

/*
* Временно выключенный тест, возможно не понадобится
test('авторизованный пользователь может успешно выйти из системы со страницы 404', function () {
    // 1. Создаем пользователя и авторизуем его
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    // 2. Заходим на несуществующий URL (это вызовет 404)
    $response = $this->get('/page-not-found-' . uniqid());
    $response->assertStatus(404);

    // 3. Имитируем отправку формы logout-form (которую должна дергать модалка)
    // Мы делаем POST запрос, так как в web.php выход защищен методом POST
    $logoutResponse = $this->post(route('logout'));

    // 4. ПРОВЕРКИ
    // А) Нас должно редиректнуть на логин
    $logoutResponse->assertRedirect(route('login'));
    
    // Б) Мы должны стать гостем
    $this->assertGuest();
}); 
*/