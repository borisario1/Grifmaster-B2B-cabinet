<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;

class TopProductsWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Топ товаров по продажам';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->withSum('orderItems', 'qty')
                    ->orderByDesc('order_items_sum_qty')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('article')
                    ->label('Артикул'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Наименование')
                    ->limit(50),
                Tables\Columns\TextColumn::make('order_items_sum_qty')
                    ->label('Продано шт.')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('product_type')
                    ->label('Тип'),
                Tables\Columns\TextColumn::make('free_stock')
                    ->label('Остаток'),
            ])
            ->paginated(false);
    }
}
