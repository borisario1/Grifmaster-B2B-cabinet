<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code_1c' => 'ID' . fake()->unique()->numberBetween(100, 999),
            'article' => 'ART-' . fake()->unique()->word(),
            'name' => fake()->words(3, true),
            'brand' => 'GrifBrand',
            'price' => fake()->randomFloat(2, 100, 10000),
            'free_stock' => fake()->numberBetween(0, 50),
        ];
    }
}
