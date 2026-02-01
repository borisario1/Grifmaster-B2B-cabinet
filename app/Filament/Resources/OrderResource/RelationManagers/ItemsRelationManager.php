<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Название товара')
                    ->maxLength(255),
                Forms\Components\TextInput::make('article')
                    ->label('Артикул')
                    ->maxLength(50),
                Forms\Components\TextInput::make('qty')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->label('Количество'),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->label('Цена')
                    ->suffix('₽'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Товар')
                    ->searchable(),
                Tables\Columns\TextColumn::make('article')
                    ->label('Артикул')
                    ->searchable(),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Кол-во')
                    ->numeric(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->money('rub'),
                Tables\Columns\TextColumn::make('total') // Рассчитываемое поле, может потребовать accessor в модели
                    ->label('Сумма')
                    ->state(function ($record) {
                        return $record->qty * $record->price;
                    })
                    ->money('rub'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить товар'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
