<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Заказы';

    public static function getModelLabel(): string
    {
        return 'Заказ';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Заказы';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Основная информация')
                            ->schema([
                                Forms\Components\Placeholder::make('order_code')
                                    ->label('Номер заказа')
                                    ->content(fn (Order $record): string => $record->order_code),

                                Forms\Components\Select::make('status_id')
                                    ->label('Статус')
                                    ->options(fn () => \App\Models\OrderStatus::ordered()->pluck('label', 'id'))
                                    ->required()
                                    ->reactive(),
                                
                                Forms\Components\Select::make('admin_id')
                                    ->label('Менеджер')
                                    ->options(fn () => \App\Models\User::whereIn('role', ['admin', 'manager'])->get()->pluck('name', 'id'))
                                    ->searchable(),

                                Forms\Components\Placeholder::make('created_at')
                                    ->label('Дата создания')
                                    ->content(fn (Order $record): string => $record->created_at->format('d.m.Y H:i')),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Финансы')
                            ->schema([
                                Forms\Components\Placeholder::make('total_amount_view')
                                    ->label('Сумма')
                                    ->content(fn (Order $record): string => number_format($record->total_amount, 2, '.', ' ') . ' ₽'),
                                
                                Forms\Components\TextInput::make('total_amount')
                                    ->label('Сумма (число)')
                                    ->numeric()
                                    ->required()
                                    ->hidden(fn ($operation) => $operation === 'view'), // Скрываем в просмотре, показываем при редактировании если нужно править
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Плательщик')
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Пользователь')
                                    ->relationship('user', 'email') // Или используем accessor name если он есть
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->disabled(), // Обычно нельзя менять владельца

                                Forms\Components\TextInput::make('org_name')
                                    ->label('Организация')
                                    ->disabled(),
                                
                                Forms\Components\TextInput::make('org_inn')
                                    ->label('ИНН')
                                    ->disabled(),
                            ]),

                        Forms\Components\Section::make('Дополнительно')
                            ->schema([
                                Forms\Components\Textarea::make('comment')
                                    ->label('Комментарий заказчика')
                                    ->rows(3)
                                    ->disabled(),
                                
                                Forms\Components\Textarea::make('closure_comment')
                                    ->label('Комментарий закрытия')
                                    ->rows(3),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('Информация о заказе')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('order_code')
                            ->label('Номер заказа')
                            ->weight('bold')
                            ->copyable(),
                        \Filament\Infolists\Components\TextEntry::make('created_at')
                            ->label('Дата создания')
                            ->dateTime('d.m.Y H:i'),
                        \Filament\Infolists\Components\TextEntry::make('orderStatus.label')
                            ->label('Статус')
                            ->badge()
                            ->color(fn ($record) => $record->orderStatus->color ?? 'gray'),
                        \Filament\Infolists\Components\TextEntry::make('total_amount')
                            ->label('Сумма')
                            ->money('rub')
                            ->weight('bold')
                            ->size(\Filament\Infolists\Components\TextEntry\TextEntrySize::Large),
                    ])
                    ->columns(4),
                
                \Filament\Infolists\Components\Section::make('Плательщик')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('user.name')
                            ->label('Пользователь'),
                        \Filament\Infolists\Components\TextEntry::make('user.email')
                            ->label('Email'),
                        \Filament\Infolists\Components\TextEntry::make('org_name')
                            ->label('Организация')
                            ->placeholder('Частное лицо'),
                        \Filament\Infolists\Components\TextEntry::make('org_inn')
                            ->label('ИНН'),
                    ])
                    ->columns(4),
                
                \Filament\Infolists\Components\Section::make('Менеджер')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('admin.name')
                            ->label('Ответственный')
                            ->placeholder('Не назначен'),
                        \Filament\Infolists\Components\TextEntry::make('comment')
                            ->label('Комментарий заказчика')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_code')
                    ->label('Номер')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('org_name')
                    ->label('Организация')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Сумма')
                    ->money('rub')
                    ->sortable(),
                Tables\Columns\TextColumn::make('orderStatus.label')
                    ->label('Статус')
                    ->badge()
                    ->color(fn ($record) => $record->orderStatus->color ?? 'gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('admin.name')
                    ->label('Менеджер')
                    ->placeholder('Не назначен'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status_id')
                    ->label('Статус')
                    ->multiple()
                    ->relationship('orderStatus', 'label'),
                Tables\Filters\SelectFilter::make('admin_id')
                    ->label('Менеджер')
                    ->options(fn () => \App\Models\User::whereIn('role', ['admin', 'manager'])->get()->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('change_status')
                    ->label('Статус')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('status_id')
                            ->label('Новый статус')
                            ->options(fn () => \App\Models\OrderStatus::ordered()->pluck('label', 'id'))
                            ->required(),
                        Forms\Components\Textarea::make('comment')
                            ->label('Комментарий')
                            ->rows(3),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $record->update([
                            'status_id' => $data['status_id'],
                            'closure_comment' => $data['comment'] ?? null,
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\HistoryRelationManager::class,
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}/view'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
