<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketsStatsOverview extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $newTickets = Ticket::where('status', 'new')->count();
        $inProgress = Ticket::where('status', 'in_progress')->count();
        $replied = Ticket::where('status', 'replied')->count();

        return [
            Stat::make('Новые обращения', $newTickets)
                ->description('Ожидают реакции')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('danger'),
                
            Stat::make('В работе', $inProgress)
                ->description('Решаются')
                ->color('warning'),

            Stat::make('Ответ получен', $replied)
                ->description('Ожидают ответа пользователя')
                ->color('success'),
        ];
    }
}
