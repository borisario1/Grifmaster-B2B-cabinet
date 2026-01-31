<?php

namespace App\Filament\Resources;

use App\Models\Brand;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Resources\BrandResource\Pages;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    
    protected static ?string $navigationGroup = 'Файлы';
    
    protected static ?string $navigationLabel = 'Бренды';
    
    protected static ?int $navigationSort = 1;
    
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Основная информация')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->label('Название')
                        ->maxLength(255),
                    
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug (URL)')
                        ->helperText('Оставьте пустым для автогенерации')
                        ->maxLength(255),
                    
                    Forms\Components\TextInput::make('logo_path')
                        ->label('Путь к логотипу')
                        ->helperText('Например: /img/brands_logo/paini.png')
                        ->maxLength(255),
                    
                    Forms\Components\TextInput::make('origin_country')
                        ->label('Страна происхождения')
                        ->maxLength(255),
                    
                    Forms\Components\TextInput::make('production_country')
                        ->label('Страна производства')
                        ->maxLength(255),
                    
                    Forms\Components\TextInput::make('priority')
                        ->numeric()
                        ->default(0)
                        ->label('Приоритет (для сортировки)')
                        ->helperText('Чем выше значение, тем выше в списке'),
                    
                    Forms\Components\Toggle::make('is_active')
                        ->default(true)
                        ->label('Активен'),
                ]),
        ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')
                    ->label('Логотип')
                    ->getStateUsing(fn ($record) => $record->logo_path ? asset($record->logo_path) : null)
                    ->defaultImageUrl('/img/placeholder.png')
                    ->height(40),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('origin_country')
                    ->label('Страна происхождения')
                    ->default('—'),
                
                Tables\Columns\TextColumn::make('production_country')
                    ->label('Страна производства')
                    ->default('—'),
                
                Tables\Columns\TextColumn::make('resources_count')
                    ->counts('resources')
                    ->label('Документов')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('priority')
                    ->label('Приоритет')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Активен'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активность')
                    ->placeholder('Все')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('priority', 'desc');
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
