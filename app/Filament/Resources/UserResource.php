<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Пользователи';

    protected static ?string $navigationLabel = 'Пользователи';

    protected static ?string $modelLabel = 'Пользователь';

    protected static ?string $pluralModelLabel = 'Пользователи';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->label('Пароль'),
                Forms\Components\Select::make('role')
                    ->options(fn ($record) => 
                        // Если редактируемый пользователь — админ, показываем все опции
                        // Иначе убираем 'admin' из списка (нельзя повысить до админа)
                        $record?->role === 'admin' ? [
                            'admin' => 'Администратор',
                            'director' => 'Руководитель',
                            'manager' => 'Менеджер',
                            'user' => 'Пользователь',
                            'partner' => 'Партнёр',
                        ] : [
                            'director' => 'Руководитель',
                            'manager' => 'Менеджер',
                            'user' => 'Пользователь',
                            'partner' => 'Партнёр',
                        ]
                    )
                    ->default('user')
                    ->required()
                    ->label('Роль')
                    ->disabled(fn ($record): bool => 
                        // Нельзя менять роль админа
                        $record?->role === 'admin'
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Роль')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'manager' => 'warning',
                        'user' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Создан'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('impersonate')
                    ->label('Войти')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-eye')
                    ->modalHeading('Просмотр личного кабинета')
                    ->modalDescription(fn (User $record) => new \Illuminate\Support\HtmlString(
                        'Вы уверены, что хотите войти под пользователем?<br><br>' .
                        '<b>ID:</b> ' . $record->id . '<br>' .
                        '<b>Email:</b> ' . e($record->email) . '<br>' .
                        '<b>ФИО:</b> ' . ($record->profile ? e($record->profile->first_name . ' ' . $record->profile->last_name) : 'Не указано') . '<br><br>' .
                        'Сессия будет активна <b>3 минуты</b>.<br>' .
                        'Это не создаст неудобств пользователю, если он сейчас работает в бизнес-кабинете.'
                    ))
                    ->modalSubmitActionLabel('Войти')
                    ->action(function (User $record, \Livewire\Component $livewire) {
                        $url = \App\Http\Controllers\ImpersonationController::generateTokenForUser(
                            $record, 
                            auth('admin')->id()
                        );
                        
                        // Отправляем JS событие для открытия в новом окне
                        $livewire->js("window.open('{$url}', '_blank')");
                    })
                    ->visible(fn (User $record): bool => 
                        auth('admin')->user()->role === 'admin' && 
                        auth('admin')->id() !== $record->id &&
                        $record->role !== 'admin'
                    ),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (User $record): bool => 
                        $record->role === 'admin' || 
                        auth()->id() === $record->id
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            // Фильтруем: убираем админов и себя
                            return $records->filter(fn ($user) => 
                                $user->role !== 'admin' && 
                                auth()->id() !== $user->id
                            );
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
