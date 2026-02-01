<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use App\Models\TicketAttachment;
use App\Services\TicketService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'Messages';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('message')
                    ->required()
                    ->label('Сообщение')
                    ->columnSpanFull(),
                
                Forms\Components\FileUpload::make('temp_attachments')
                    ->label('Вложения')
                    ->multiple()
                    ->maxFiles(5)
                    ->maxSize(102400) // 100MB per file
                    ->disk('private')
                    ->directory('temp-uploads')
                    ->visibility('private')
                    ->columnSpanFull()
                    ->helperText('До 5 файлов, макс. 100 МБ каждый'),
                
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('message')
            ->modifyQueryUsing(fn ($query) => $query->with('attachments'))
            ->columns([
                Tables\Columns\TextColumn::make('sender_type')
                    ->label('Тип')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'success',
                        'user' => 'info',
                        'system' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Поддержка',
                        'user' => 'Клиент',
                        'system' => 'Система',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('message')
                    ->label('Сообщение')
                    ->wrap()
                    ->limit(150)
                    ->description(function ($record) {
                        if ($record->attachments->isEmpty()) {
                            return null;
                        }
                        $links = $record->attachments->map(function ($att) {
                            $url = route('tickets.attachment', $att->id);
                            return "<a href='{$url}' target='_blank' class='text-primary-600 hover:underline'>📎 {$att->original_name}</a>";
                        })->join(' ');
                        return new HtmlString($links);
                    }),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'asc')
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Написать ответ')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        $data['sender_type'] = 'admin';
                        
                        // Обновляем тикет
                        $ticket = $this->getOwnerRecord();
                        $ticket->update([
                            'last_reply_at' => now(),
                            'last_reply_by' => 'admin',
                            'status' => 'waiting_reply'
                        ]);
                        
                        return $data;
                    })
                    ->after(function ($record, array $data) {
                        // Обрабатываем загруженные файлы
                        if (!empty($data['temp_attachments'])) {
                            $ticket = $this->getOwnerRecord();
                            
                            foreach ($data['temp_attachments'] as $tempPath) {
                                $originalName = basename($tempPath);
                                $newPath = "ticket-attachments/{$ticket->id}/" . uniqid() . '_' . $originalName;
                                
                                if (Storage::disk('private')->exists($tempPath)) {
                                    // Перемещаем файл
                                    Storage::disk('private')->move($tempPath, $newPath);
                                    
                                    TicketAttachment::create([
                                        'message_id' => $record->id,
                                        'file_path' => $newPath,
                                        'original_name' => $originalName,
                                        'mime_type' => Storage::disk('private')->mimeType($newPath) ?? 'application/octet-stream',
                                        'size' => Storage::disk('private')->size($newPath),
                                        'created_at' => now(),
                                    ]);
                                }
                            }
                        }
                    }),
            ])
            ->actions([])
            ->bulkActions([]);
    }
}
