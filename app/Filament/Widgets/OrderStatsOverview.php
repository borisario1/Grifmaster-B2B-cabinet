<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $newOrders = \App\Models\Order::whereHas('orderStatus', fn($q) => $q->where('name', 'new'))->count();
        $processingOrders = \App\Models\Order::whereHas('orderStatus', fn($q) => $q->where('name', 'processing'))->count();
        $completedOrders = \App\Models\Order::whereHas('orderStatus', fn($q) => $q->where('name', 'completed'))->count();

        return [
            Stat::make('Новые заказы', $newOrders)
                ->description('Требуют обработки')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            
            Stat::make('В обработке', $processingOrders)
                ->description('В процессе сборки')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            
            Stat::make('Выполнены', $completedOrders)
                ->description('Всего завершено')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('primary'),
        ];
    }
}
