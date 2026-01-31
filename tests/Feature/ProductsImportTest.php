<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

/**
 * Тест 1: Проверка создания нового товара и обновления даты синхронизации
 */
test('система корректно импортирует товары из внешнего CSV файла', function () {
    // Подменяем ответ от внешнего сервера (Mock)
    $csvData = "code_1c,name,free_stock,article,brand,ignored,price,ignored,ignored,ignored,status,type,category,collection,image\n" .
               "ID001,Тестовый Товар,15,ART-UNIQUE-01,GrifBrand,0,2500.00,0,0,0,Сток,Тип1,Кат1,Колл1,test.jpg";

    Http::fake([
        '*' => Http::response($csvData, 200)
    ]);

    // Запускаем команду импорта
    Artisan::call('products:import');

    // Проверяем, появился ли товар в базе
    $product = Product::where('article', 'ART-UNIQUE-01')->first();

    expect($product)->not->toBeNull()
        ->and($product->name)->toBe('Тестовый Товар')
        ->and($product->last_synced_at)->not->toBeNull()
        ->and($product->last_synced_at->isToday())->toBeTrue();
});

/**
 * Тест 2: Проверка обновления существующего товара (Update)
 */
test('система обновляет данные существующего товара и дату синхронизации', function () {
    // Фиксируем время для точности теста
    Carbon::setTestNow(now()->subHours(5));
    
    // Создаем товар "вчерашним" днем
    $product = Product::create([
        'article' => 'UPDATE-ME',
        'name' => 'Старое имя',
        'price' => 1000,
        'brand' => 'OldBrand',
        'code_1c' => 'OLD_ID',
        'last_synced_at' => now()->subDay()
    ]);

    // Подменяем CSV новыми данными
    $newPrice = 1500.00;
    $csvData = "code_1c,name,free_stock,article,brand,ignored,price,ignored,ignored,ignored,status,type,category,collection,image\n" .
               "OLD_ID,Новое имя,20,UPDATE-ME,OldBrand,0,{$newPrice},0,0,0,Сток,Тип,Кат,Колл,img.jpg";

    Http::fake(['*' => Http::response($csvData, 200)]);

    // Возвращаем время к настоящему
    Carbon::setTestNow();

    Artisan::call('products:import');

    $product->refresh();

    expect($product->price)->toEqual($newPrice)
        ->and($product->name)->toBe('Новое имя')
        ->and($product->last_synced_at->gt(now()->subMinute()))->toBeTrue();
});

/**
 * Тест 3: Проверка поведения при пустом файле
 */
test('база данных не очищается при получении пустого файла импорта', function () {
    $initialData = [
        'article' => 'EXISTING-01',
        'name' => 'Важный товар',
        'free_stock' => 10
    ];
    $product = Product::factory()->create($initialData);

    Http::fake(['*' => Http::response("", 200)]);
    Artisan::call('products:import');

    $product->refresh();
    // Проверяем, что данные не затерлись пустыми значениями
    expect(Product::count())->toBe(1)
        ->and($product->name)->toBe($initialData['name'])
        ->and($product->free_stock)->toBe($initialData['free_stock']);
});