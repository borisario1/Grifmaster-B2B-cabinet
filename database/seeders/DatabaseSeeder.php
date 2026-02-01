<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Создаем тестового пользователя
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'password' => bcrypt('password'),
                'role' => 'admin', // Даем права админа для доступа к панели
                'status' => 'active',
            ]
        );

        // Создаем профиль для имени
        if (!$user->profile()->exists()) {
            $user->profile()->create([
                 'first_name' => 'Test',
                 'last_name' => 'User',
            ]);
        }

        $this->call([
            BrandSeeder::class,
            ResourceSeeder::class,
            OrderStatusSeeder::class,
        ]);
    }
}
