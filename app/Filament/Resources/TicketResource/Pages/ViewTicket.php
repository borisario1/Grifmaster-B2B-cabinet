<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Services\NotificationService;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Facades\Storage;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;
    
    protected static string $view = 'filament.resources.ticket-resource.pages.view-ticket';
    
    public ?string $replyMessage = null;
    public ?array $replyAttachments = [];
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('close')
                ->label('Закрыть обращение')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'closed']);
                    Notification::make()
                        ->success()
                        ->title('Обращение закрыто')
                        ->send();
                })
                ->visible(fn () => $this->record->status !== 'closed'),
        ];
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Информация об обращении')
                    ->schema([
                        Infolists\Components\TextEntry::make('request_code')
                            ->label('Номер обращения'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Статус')
                            ->badge()
                            ->formatStateUsing(function (string $state): string {
                                return Ticket::STATUSES[$state] ?? $state;
                            })
                            ->color(fn (Ticket $record): string => match ($record->status) {
                                'new' => 'danger',
                                'in_progress' => 'warning',
                                'waiting_reply' => 'info',
                                'closed' => 'success',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('category')
                            ->label('Категория')
                            ->formatStateUsing(function (string $state): string {
                                return Ticket::CATEGORIES[$state] ?? $state;
                            }),
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Пользователь')
                            ->default('—'),
                        Infolists\Components\TextEntry::make('admin.email')
                            ->label('Назначен')
                            ->default('—'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Создано')
                            ->dateTime('d.m.Y H:i'),
                    ])
                    ->columns(3),
            ]);
    }
    
    protected function getForms(): array
    {
        return array_merge(parent::getForms(), [
            'replyForm' => $this->makeForm()
                ->schema([
                    Forms\Components\Textarea::make('replyMessage')
                        ->label('Ваш ответ')
                        ->required()
                        ->rows(5)
                        ->columnSpanFull()
                        ->placeholder('Введите ваш ответ...'),
                    
                    Forms\Components\FileUpload::make('replyAttachments')
                        ->label('Вложения')
                        ->multiple()
                        ->maxFiles(5)
                        ->maxSize(102400)
                        ->disk('private')
                        ->directory('temp-uploads')
                        ->visibility('private')
                        ->columnSpanFull()
                        ->helperText('До 5 файлов, макс. 100 МБ каждый'),
                ])
                ->statePath(''),
        ]);
    }
    
    public function sendReply(): void
    {
        $data = $this->replyForm->getState();
        
        if (empty($data['replyMessage'])) {
            Notification::make()
                ->danger()
                ->title('Ошибка')
                ->body('Сообщение не может быть пустым')
                ->send();
            return;
        }
        
        // Создаем сообщение от админа
        $message = TicketMessage::create([
            'request_id' => $this->record->id,
            'sender_type' => 'admin',
            'sender_id' => auth()->id(),
            'message' => $data['replyMessage'],
            'is_read' => false,
            'created_at' => now(),
        ]);
        
        // Обрабатываем вложения
        $attachmentsCount = 0;
        if (!empty($data['replyAttachments'])) {
            foreach ($data['replyAttachments'] as $tempPath) {
                $originalName = basename($tempPath);
                $newPath = "ticket-attachments/{$this->record->id}/" . uniqid() . '_' . $originalName;
                
                if (Storage::disk('private')->exists($tempPath)) {
                    Storage::disk('private')->move($tempPath, $newPath);
                    
                    TicketAttachment::create([
                        'message_id' => $message->id,
                        'file_path' => $newPath,
                        'original_name' => $originalName,
                        'mime_type' => Storage::disk('private')->mimeType($newPath) ?? 'application/octet-stream',
                        'size' => Storage::disk('private')->size($newPath),
                        'created_at' => now(),
                    ]);
                    $attachmentsCount++;
                }
            }
        }
        
        // Обновляем информацию о последнем ответе
        $this->record->update([
            'last_reply_at' => now(),
            'last_reply_by' => 'admin',
            'status' => $this->record->status === 'new' ? 'in_progress' : 'waiting_reply',
        ]);
        
        // Формируем контекст для уведомления
        $context = [
            'ticket_code' => $this->record->request_code,
            'attachments_count' => $attachmentsCount,
        ];
        
        // Отправляем уведомление пользователю
        app(NotificationService::class)->send(
            $this->record->user_id,
            'ticket_reply',
            'Новый ответ на обращение',
            "Получен ответ на ваше обращение №{$this->record->request_code}" . 
                ($attachmentsCount > 0 ? "\n📎 Прикреплено файлов: {$attachmentsCount}" : ''),
            route('tickets.show', $this->record->request_code),
            $context
        );
        
        // Очищаем форму
        $this->replyForm->fill(['replyMessage' => null, 'replyAttachments' => []]);
        $this->replyMessage = null;
        $this->replyAttachments = [];
        
        Notification::make()
            ->success()
            ->title('Ответ отправлен')
            ->send();
    }
}
