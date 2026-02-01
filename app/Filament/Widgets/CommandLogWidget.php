<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CommandLogWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Последние системные задачи';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\CommandLog::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('command')
                    ->label('Команда')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'running' => 'warning',
                        'pending' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Начало')
                    ->dateTime('d.m.Y H:i:s'),
                Tables\Columns\TextColumn::make('finished_at')
                    ->label('Конец')
                    ->dateTime('d.m.Y H:i:s'),
                Tables\Columns\TextColumn::make('error')
                    ->label('Ошибка')
                    ->color('danger')
                    ->limit(50)
                    ->tooltip(fn ($state) => $state),
            ])
            ->poll('5s')
            ->paginated(false)
            ->actions([
                Tables\Actions\Action::make('view_output')
                    ->label('Лог')
                    ->icon('heroicon-o-eye')
                    ->modalContent(fn ($record) => view('filament.pages.command-log-modal', ['record' => $record]))
                    ->modalHeading('Лог выполнения')
            ]);
    }
}
