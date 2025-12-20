<?php

use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Указываем, что используем RefreshDatabase И наследуемся от TestCase
uses(TestCase::class, RefreshDatabase::class);

test('организация может быть создана и привязана к пользователю', function () {
    // Теперь $this->app будет существовать, но Laravel 12 
    // должен сам справиться с Hash, если мы наследуем TestCase.
    // Строку $this->app->make('hash') можно пока убрать.

    $user = User::factory()->create();

    $organization = Organization::create([
        'user_id' => $user->id,
        'name'    => 'ООО ТЕСТ',
        'inn'     => '1234567890',
        'type'    => 'org',
    ]);

    expect($organization->name)->toBe('ООО ТЕСТ');
    
    $this->assertDatabaseHas('organizations', [
        'inn' => '1234567890'
    ]);
});