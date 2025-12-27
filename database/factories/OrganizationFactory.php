<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Указываем имя модели, для которой эта фабрика создает данные.
     */
    protected $model = Organization::class;

    /**
     * Определение состояния модели по умолчанию.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Автоматически создаем пользователя, если он не передан
            'user_id' => User::factory(),
            
            // Генерация случайного названия компании (например, "ООО Вектор")
            'name' => $this->faker->company(),
            
            // Генерация случайного ИНН (10 цифр для юр. лиц)
            'inn' => $this->faker->numerify('##########'),
            
            // Генерация КПП (9 цифр)
            'kpp' => $this->faker->numerify('#########'),
            
            // Тип организации по умолчанию
            'type' => 'org',
            
            // Случайный адрес
            'address' => $this->faker->address(),
            
            // ОГРН (13 цифр для юр. лиц)
            'ogrn' => $this->faker->numerify('#############'),
        ];
    }
}