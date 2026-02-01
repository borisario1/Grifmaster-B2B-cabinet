<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers;
use App\Models\Ticket;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = '🎫 Поддержка';
    
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Обращения';

    protected static ?string $modelLabel = 'Обращение';

    protected static ?string $pluralModelLabel = 'Обращения';
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::unreadByAdmin()->count() ?: null;
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация о пользователе')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->required()
                            ->label('Пользователь')
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\TextInput::make('user_email')
                            ->email()
                            ->label('Email')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('user_phone')
                            ->tel()
                            ->label('Телефон')
                            ->maxLength(255),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Организация')
                    ->schema([
                        Forms\Components\TextInput::make('org_name')
                            ->label('Название')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('org_inn')
                            ->label('ИНН')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('org_kpp')
                            ->label('КПП')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('org_ogrn')
                            ->label('ОГРН')
                            ->maxLength(255),
                    ])
                    ->columns(4)
                    ->collapsible(),
                
                Forms\Components\Section::make('Обращение')
                    ->schema([
                        Forms\Components\Select::make('category')
                            ->options(Ticket::CATEGORIES)
                            ->required()
                            ->label('Категория'),
                        
                        Forms\Components\TextInput::make('topic')
                            ->required()
                            ->label('Тема')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        
                        Forms\Components\Select::make('status')
                            ->options(Ticket::STATUSES)
                            ->required()
                            ->default('new')
                            ->label('Статус'),
                        
                        Forms\Components\Select::make('admin_id')
                            ->relationship('admin', 'email')
                            ->label('Назначен админ')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('request_code')
                            ->label('Код обращения')
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('request_code')
                    ->label('№')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'danger' => 'new',
                        'warning' => 'in_progress',
                        'info' => 'waiting_reply',
                        'success' => 'closed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'Новое',
                        'in_progress' => 'В работе',
                        'waiting_reply' => 'Ожидает ответа',
                        'closed' => 'Закрыто',
                        default => $state,
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('category')
                    ->label('Категория')
                    ->formatStateUsing(fn ($record) => $record->category_label)
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('topic')
                    ->label('Тема')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->topic),
                
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Пользователь')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('admin.email')
                    ->label('Назначен')
                    ->default('—')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('last_reply_at')
                    ->label('Последний ответ')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\IconColumn::make('last_reply_by')
                    ->label('Ответил')
                    ->icon(fn (string $state = null): string => match ($state) {
                        'user' => 'heroicon-o-user',
                        'admin' => 'heroicon-o-shield-check',
                        default => 'heroicon-o-minus',
                    })
                    ->color(fn (string $state = null): string => match ($state) {
                        'user' => 'warning',
                        'admin' => 'success',
                        default => 'gray',
                    })
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'new' => 'Новое',
                        'in_progress' => 'В работе',
                        'waiting_reply' => 'Ожидает ответа',
                        'closed' => 'Закрыто',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('category')
                    ->label('Категория')
                    ->options(Ticket::CATEGORIES)
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('admin_id')
                    ->label('Назначен админ')
                    ->relationship('admin', 'email')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\Filter::make('unread')
                    ->label('Непрочитанные')
                    ->query(fn (Builder $query): Builder => $query->unreadByAdmin()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('assign_to_me')
                    ->label('Назначить мне')
                    ->icon('heroicon-o-user-plus')
                    ->action(function (Ticket $record) {
                        $record->update(['admin_id' => auth()->id()]);
                    })
                    ->visible(fn (Ticket $record) => !$record->admin_id)
                    ->color('success'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('assign_to_me')
                        ->label('Назначить мне')
                        ->icon('heroicon-o-user-plus')
                        ->action(function ($records) {
                            $records->each->update(['admin_id' => auth()->id()]);
                        })
                        ->color('success')
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MessagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
