<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $usersCount = User::count();
        $usersToday = User::whereDate('created_at', today())->count();
        
        $orgsCount = Organization::count();
        
        $ticketsOpen = Ticket::where('status', 'open')->count();
        $ticketsTotal = Ticket::count();
        
        $ordersToday = DB::table('b2b_orders')->whereDate('created_at', today())->count();

        return [
            Stat::make('Пользователей', $usersCount)
                ->description("+{$usersToday} сегодня")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Организаций', $orgsCount)
                ->description('Активных')
                ->color('info'),
            Stat::make('Открытых обращений', $ticketsOpen)
                ->description("из {$ticketsTotal} всего")
                ->color($ticketsOpen > 5 ? 'danger' : 'warning'),
            Stat::make('Заказов сегодня', $ordersToday)
                ->description('За текущий день')
                ->color('primary'),
        ];
    }
}
