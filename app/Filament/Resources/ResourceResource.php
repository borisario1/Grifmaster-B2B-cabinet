<?php

namespace App\Filament\Resources;

use App\Models\Resource;
use App\Models\Brand;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource as FilamentResource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Resources\ResourceResource\Pages;

class ResourceResource extends FilamentResource
{
    protected static ?string $model = Resource::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = '🎫 Поддержка';
    
    protected static ?string $navigationLabel = 'Ресурсы (файлы)';
    
    protected static ?int $navigationSort = 3;
    
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Основная информация')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->label('Название документа')
                        ->maxLength(255),
                    
                    Forms\Components\Textarea::make('description')
                        ->label('Описание')
                        ->rows(3)
                        ->maxLength(65535)
                        ->columnSpanFull(),
                    
                    Forms\Components\Select::make('type')
                        ->options([
                            'price_list' => 'Прайс-лист',
                            'certificate' => 'Сертификат',
                            'catalog' => 'Каталог',
                            '3d_model' => '3D модель',
                            'video' => 'Видео',
                            'other' => 'Другое',
                        ])
                        ->required()
                        ->label('Тип'),
                    
                    Forms\Components\Select::make('brand_id')
                        ->relationship('brand', 'name')
                        ->label('Бренд')
                        ->helperText('Оставьте пустым для общих документов')
                        ->searchable()
                        ->preload(),
                    
                    Forms\Components\Toggle::make('is_active')
                        ->default(true)
                        ->label('Активен'),
                    
                    Forms\Components\Toggle::make('is_pinned')
                        ->label('Закрепить в блоке "Общие документы"')
                        ->helperText('Документ будет показан вверху страницы'),
                ]),
            
            Forms\Components\Section::make('Файл')
                ->schema([
                    Forms\Components\FileUpload::make('file_path')
                        ->label('Файл')
                        ->disk('local')
                        ->directory('resources')
                        ->visibility('private')
                        ->acceptedFileTypes(['application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'video/mp4', 'video/quicktime'])
                        ->maxSize(102400)
                        ->helperText('Максимум 100 МБ. Оставьте пустым, если используете внешнюю ссылку')
                        ->downloadable(),
                    
                    Forms\Components\TextInput::make('external_link')
                        ->url()
                        ->label('Внешняя ссылка')
                        ->helperText('Для веб-версий. Если указано, file_path игнорируется')
                        ->maxLength(255),
                ]),
            
            Forms\Components\Section::make('Подтверждение')
                ->schema([
                    Forms\Components\Toggle::make('require_confirmation')
                        ->label('Требовать подтверждение перед скачиванием')
                        ->reactive(),
                    
                    Forms\Components\RichEditor::make('confirmation_text')
                        ->label('Текст подтверждения')
                        ->visible(fn ($get) => $get('require_confirmation')),
                    
                    Forms\Components\TextInput::make('confirm_btn_text')
                        ->label('Текст кнопки подтверждения')
                        ->default('Скачать')
                        ->visible(fn ($get) => $get('require_confirmation'))
                        ->maxLength(255),
                ]),
        ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(true)
                    ->toggledHiddenByDefault(),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Тип')
                    ->colors([
                        'primary' => 'price_list',
                        'success' => 'certificate',
                        'warning' => 'catalog',
                        'info' => '3d_model',
                        'danger' => 'video',
                        'secondary' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'price_list' => 'Прайс',
                        'certificate' => 'Сертификат',
                        'catalog' => 'Каталог',
                        '3d_model' => '3D',
                        'video' => 'Видео',
                        'other' => 'Другое',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Бренд')
                    ->default('Общий')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('stats_count')
                    ->counts('stats')
                    ->label('Скачиваний')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Активен'),
                
                Tables\Columns\IconColumn::make('is_pinned')
                    ->boolean()
                    ->label('Закреплен'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип')
                    ->options([
                        'price_list' => 'Прайс-лист',
                        'certificate' => 'Сертификат',
                        'catalog' => 'Каталог',
                        '3d_model' => '3D модель',
                        'video' => 'Видео',
                        'other' => 'Другое',
                    ]),
                
                Tables\Filters\SelectFilter::make('brand_id')
                    ->label('Бренд')
                    ->relationship('brand', 'name'),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активность'),
                
                Tables\Filters\TernaryFilter::make('is_pinned')
                    ->label('Закреплен'),
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
            ->defaultSort('created_at', 'desc');
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResources::route('/'),
            'create' => Pages\CreateResource::route('/create'),
            'edit' => Pages\EditResource::route('/{record}/edit'),
        ];
    }
}
