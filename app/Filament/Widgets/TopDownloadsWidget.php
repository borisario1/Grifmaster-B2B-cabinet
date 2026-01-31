<?php

namespace App\Filament\Widgets;

use App\Models\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopDownloadsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Resource::query()
                    ->withCount('stats')
                    ->with('brand')
                    ->orderBy('stats_count', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Название файла')
                    ->icon(fn (Resource $record): string => 'heroicon-o-document-text')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'catalog' => 'info',
                        'price_list' => 'success',
                        'certificate' => 'warning',
                        '3d_model' => 'purple',
                        'video' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'catalog' => 'Каталог',
                        'price_list' => 'Прайс-лист',
                        'certificate' => 'Сертификат',
                        '3d_model' => '3D модель',
                        'video' => 'Видео',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Бренд')
                    ->sortable()
                    ->default('—'),
                    
                Tables\Columns\TextColumn::make('stats_count')
                    ->label('Скачиваний')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлен')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->heading('Топ-10 самых скачиваемых файлов')
            ->paginated(false);
    }
}
