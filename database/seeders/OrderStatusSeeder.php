<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'new',
                'label' => 'Новый',
                'color' => '#3B82F6', // blue
                'sort_order' => 1,
                'is_default' => true,
                'is_final' => false,
            ],
            [
                'name' => 'confirmed',
                'label' => 'Подтвержден',
                'color' => '#10B981', // green
                'sort_order' => 2,
                'is_default' => false,
                'is_final' => false,
            ],
            [
                'name' => 'processing',
                'label' => 'В обработке',
                'color' => '#F59E0B', // yellow
                'sort_order' => 3,
                'is_default' => false,
                'is_final' => false,
            ],
            [
                'name' => 'shipped',
                'label' => 'Отправлен',
                'color' => '#8B5CF6', // purple
                'sort_order' => 4,
                'is_default' => false,
                'is_final' => false,
            ],
            [
                'name' => 'delivered',
                'label' => 'Доставлен',
                'color' => '#059669', // dark green
                'sort_order' => 5,
                'is_default' => false,
                'is_final' => true,
            ],
            [
                'name' => 'cancelled',
                'label' => 'Отменен',
                'color' => '#EF4444', // red
                'sort_order' => 6,
                'is_default' => false,
                'is_final' => true,
            ],
        ];

        foreach ($statuses as $status) {
            OrderStatus::updateOrCreate(
                ['name' => $status['name']],
                $status
            );
        }
    }
}
