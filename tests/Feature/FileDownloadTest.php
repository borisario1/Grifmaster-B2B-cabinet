<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Organization;
use App\Models\Brand;
use App\Models\Resource;
use App\Models\ResourceStat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware();
    
    // Создаем пользователя с профилем
    $this->user = User::factory()->create();
    UserProfile::create(['user_id' => $this->user->id]);
    
    // Создаем организацию
    $this->org = Organization::factory()->create([
        'user_id' => $this->user->id,
        'inn' => '1234567890'
    ]);
    
    $this->user->update(['selected_org_id' => $this->org->id]);
    
    // Создаем тестовый бренд
    $this->brand = Brand::create([
        'name' => 'Test Brand',
        'slug' => 'test-brand',
        'logo_path' => null
    ]);
    
    // Создаем фейковое хранилище
    Storage::fake('local');
    
    // Создаем тестовый файл
    $file = UploadedFile::fake()->create('test-document.pdf', 1024);
    $path = $file->store('documents/test');
    
    // Создаем ресурсы
    $this->resource = Resource::create([
        'title' => 'Тестовый документ',
        'type' => 'catalog',
        'file_path' => $path,
        'brand_id' => $this->brand->id,
        'is_active' => true,
        'is_pinned' => false,
        'require_confirmation' => false,
    ]);
    
    $this->confirmResource = Resource::create([
        'title' => 'Документ с подтверждением',
        'type' => 'price_list',
        'file_path' => $path,
        'brand_id' => $this->brand->id,
        'is_active' => true,
        'is_pinned' => false,
        'require_confirmation' => true,
        'confirmation_text' => 'Подтвердите скачивание',
        'confirm_btn_text' => 'Скачать',
    ]);
    
    $this->externalResource = Resource::create([
        'title' => 'Внешняя ссылка',
        'type' => 'catalog',
        'external_link' => 'https://example.com',
        'brand_id' => null,
        'is_active' => true,
        'is_pinned' => false,
        'require_confirmation' => false,
    ]);
});

test('страница файлов доступна авторизованным пользователям', function () {
    $response = $this->actingAs($this->user)->get(route('files.index'));
    
    $response->assertStatus(200);
    $response->assertViewIs('files');
    $response->assertViewHas('pinnedResources');
    $response->assertViewHas('brands');
    $response->assertViewHas('resourcesByType');
});

test('страница файлов недоступна неавторизованным пользователям', function () {
    $this->withMiddleware();
    
    $response = $this->get(route('files.index'));
    
    $response->assertRedirect(route('login'));
});

test('скачивание файла без подтверждения работает корректно', function () {
    $response = $this->actingAs($this->user)
        ->get(route('files.download', ['id' => $this->resource->id]));
    
    $response->assertStatus(200);
    $response->assertDownload();
});

test('скачивание файла с подтверждением возвращает JSON (AJAX)', function () {
    $response = $this->actingAs($this->user)
        ->get(route('files.download', ['id' => $this->confirmResource->id]));
    
    // Должен вернуть JSON с данными для модалки
    $response->assertStatus(200);
    $response->assertJson([
        'require_confirmation' => true,
        'confirmation_text' => 'Подтвердите скачивание',
        'confirm_btn_text' => 'Скачать',
    ]);
});

test('скачивание файла с параметром confirmed работает', function () {
    $response = $this->actingAs($this->user)
        ->get(route('files.download', [
            'id' => $this->confirmResource->id,
            'confirmed' => 'true'
        ]));
    
    $response->assertStatus(200);
    $response->assertDownload();
});

test('скачивание несуществующего файла возвращает 404', function () {
    $response = $this->actingAs($this->user)
        ->get(route('files.download', ['id' => 99999]));
    
    $response->assertStatus(404);
});

test('скачивание неактивного ресурса возвращает 404', function () {
    $this->resource->update(['is_active' => false]);
    
    $response = $this->actingAs($this->user)
        ->get(route('files.download', ['id' => $this->resource->id]));
    
    $response->assertStatus(404);
});

test('скачивание файла логирует статистику', function () {
    expect(ResourceStat::count())->toBe(0);
    
    $response = $this->actingAs($this->user)
        ->get(route('files.download', ['id' => $this->resource->id]));
    
    $response->assertStatus(200);
    
    // Проверяем, что статистика записана
    expect(ResourceStat::count())->toBe(1);
    
    $stat = ResourceStat::first();
    expect($stat->resource_id)->toBe($this->resource->id)
        ->and($stat->user_id)->toBe($this->user->id)
        ->and($stat->ip_address)->not->toBeNull();
});

test('внешняя ссылка перенаправляет на внешний URL', function () {
    $response = $this->actingAs($this->user)
        ->get(route('files.download', ['id' => $this->externalResource->id]));
    
    $response->assertRedirect('https://example.com');
});

test('внешняя ссылка также логирует статистику', function () {
    expect(ResourceStat::count())->toBe(0);
    
    $response = $this->actingAs($this->user)
        ->get(route('files.download', ['id' => $this->externalResource->id]));
    
    expect(ResourceStat::count())->toBe(1);
    
    $stat = ResourceStat::first();
    expect($stat->resource_id)->toBe($this->externalResource->id);
});

test('группировка по типам работает корректно', function () {
    $response = $this->actingAs($this->user)->get(route('files.index'));
    
    $resourcesByType = $response->viewData('resourcesByType');
    
    expect($resourcesByType)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    expect($resourcesByType->keys()->toArray())->toContain('catalog');
    expect($resourcesByType->keys()->toArray())->toContain('price_list');
});

test('метод getFileSize возвращает 0 если файла нет в хранилище', function () {
    // Фейковый диск пуст, файл не существует реально
    expect($this->resource->getFileSize())->toBe(0);
});

test('метод getFileSize возвращает null для внешних ссылок', function () {
    expect($this->externalResource->getFileSize())->toBeNull();
});

test('метод getDownloadCount возвращает количество скачиваний', function () {
    expect($this->resource->getDownloadCount())->toBe(0);
    
    // Добавляем статистику
    ResourceStat::create([
        'resource_id' => $this->resource->id,
        'user_id' => $this->user->id,
        'ip_address' => '127.0.0.1',
    ]);
    
    ResourceStat::create([
        'resource_id' => $this->resource->id,
        'user_id' => $this->user->id,
        'ip_address' => '127.0.0.1',
    ]);
    
    expect($this->resource->fresh()->getDownloadCount())->toBe(2);
});
