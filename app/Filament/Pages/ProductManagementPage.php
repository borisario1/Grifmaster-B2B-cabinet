<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use App\Models\CommandLog;
use Illuminate\Support\Facades\Artisan;
use Filament\Notifications\Notification;

class ProductManagementPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationLabel = 'Управление каталогом';
    protected static ?string $title = 'Сервисные операции';
    protected static ?string $slug = 'product-management';
    protected static ?string $navigationGroup = 'Система';

    protected static string $view = 'filament.pages.product-management-page';

    // Вычисляемое свойство для получения текущей запущенной команды
    public function getActiveLogProperty()
    {
        return CommandLog::where('status', 'running')
            ->orderBy('id', 'desc')
            ->first();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(CommandLog::query()->orderBy('created_at', 'desc'))
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
                Tables\Columns\TextColumn::make('user_id')
                    ->label('User ID')
                    ->numeric(),
            ])
            ->poll('5s') // Автообновление
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->form([
                         \Filament\Forms\Components\Textarea::make('output')
                            ->rows(20)
                            ->readOnly(),
                         \Filament\Forms\Components\Textarea::make('error')
                            ->rows(10)
                            ->readOnly()
                            ->hidden(fn ($record) => empty($record->error)),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import_products')
                ->label('Импорт товаров')
                ->color('primary')
                ->icon('heroicon-o-arrow-down-tray')
                ->requiresConfirmation()
                ->action(function () {
                    $this->runCommand('products:import'); 
                }),
            
            Action::make('enrich_products')
                ->label('Обогащение товаров')
                ->color('warning')
                ->icon('heroicon-o-sparkles')
                ->requiresConfirmation()
                ->action(function () {
                     $this->runCommand('products:enrich');
                }),

            Action::make('sync_all')
                ->label('Полная синхронизация')
                ->color('success')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('Запустить полную синхронизацию?')
                ->modalDescription('Будет выполнен импорт товаров из 1С, а затем их обогащение данными из внешнего API. Это может занять некоторое время.')
                ->action(function () {
                     $this->runCommand('products:sync-all');
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

        // Формируем команду для запуска в фоне
        // Используем base_path('artisan') для корректного пути
        // --log-id передаем, чтобы команда знала куда писать output
        $artisanPath = base_path('artisan');
        $command = "php {$artisanPath} {$signature} --log-id={$log->id} > /dev/null 2>&1 &";

        try {
            // Запуск в фоне
            exec($command);
            
            // Сразу ставим статус running, хотя команда сама это сделает при старте initLog
            // Но чтобы в UI сразу была реакция
            $log->update(['started_at' => now(), 'status' => 'running']);

            Notification::make()
                ->title('Процесс запущен в фоне')
                ->body('Вы можете следить за статусом в журнале ниже.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            Notification::make()
                ->title('Ошибка запуска')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
