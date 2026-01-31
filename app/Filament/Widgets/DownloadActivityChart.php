<?php

namespace App\Filament\Widgets;

use App\Models\ResourceStat;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DownloadActivityChart extends ChartWidget
{
    protected static ?string $heading = 'Активность скачиваний за последние 30 дней';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Получаем данные за последние 30 дней
        $stats = ResourceStat::selectRaw('DATE(downloaded_at) as date, COUNT(*) as count')
            ->where('downloaded_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Создаем массив всех дат за последние 30 дней
        $dates = [];
        $counts = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[] = now()->subDays($i)->format('d.m');
            
            // Ищем количество скачиваний для этой даты
            $stat = $stats->firstWhere('date', $date);
            $counts[] = $stat ? $stat->count : 0;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Скачивания',
                    'data' => $counts,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $dates,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
