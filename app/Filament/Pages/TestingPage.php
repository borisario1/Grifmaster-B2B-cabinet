<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use App\Models\CommandLog;
use Filament\Notifications\Notification;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;

class TestingPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationLabel = 'Тестирование системы';
    protected static ?string $title = 'Диагностика и тесты';
    protected static ?string $slug = 'system-testing';
    protected static ?string $navigationGroup = 'Система';

    protected static string $view = 'filament.pages.testing-page';

    // Для виджета прогресса
    public function getActiveLogProperty()
    {
        return CommandLog::where('status', 'running')
            ->where('command', 'like', 'system:test%')
            ->orderBy('id', 'desc')
            ->first();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(CommandLog::query()->where('command', 'like', 'system:test%')->orderBy('created_at', 'desc'))
            ->columns([
                Tables\Columns\TextColumn::make('command')
                    ->label('Тест')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Результат')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'running' => 'warning',
                        'pending' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Запуск')
                    ->dateTime('d.m.Y H:i:s'),
                Tables\Columns\TextColumn::make('finished_at')
                     ->label('Завершение')
                    ->dateTime('d.m.Y H:i:s'),
            ])
            ->poll('5s')
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->form([
                         \Filament\Forms\Components\Textarea::make('output')
                            ->rows(20)
                            ->readOnly()
                            ->extraAttributes(['class' => 'font-mono text-xs']),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('run_all_tests')
                ->label('Запустить все тесты')
                ->color('danger')
                ->icon('heroicon-o-play')
                ->requiresConfirmation()
                ->action(function () {
                    $this->runCommand('system:test');
                }),
        ];
    }

    protected function runCommand(string $signature)
    {
        $log = CommandLog::create([
            'command' => $signature,
            'status' => 'pending', 
            'user_id' => auth()->id(),
            'created_at' => now(),
        ]);

        $artisanPath = base_path('artisan');
        // Запускаем через nohup/&, передаем log-id
        $command = "php {$artisanPath} {$signature} --log-id={$log->id} > /dev/null 2>&1 &";

        try {
            exec($command);
            
            $log->update(['started_at' => now(), 'status' => 'running']);

            Notification::make()
                ->title('Тестирование запущено')
                ->body('Результаты появятся в таблице.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            $log->update(['status' => 'failed', 'error' => $e->getMessage()]);
            Notification::make()
                ->title('Ошибка запуска')
                ->danger()
                ->send();
        }
    }
}
