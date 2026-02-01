<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Services\NotificationService;

class TicketObserver
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        // Проверяем изменение статуса
        if ($ticket->isDirty('status')) {
            $oldStatus = $ticket->getOriginal('status');
            $newStatus = $ticket->status;
            
            $oldStatusLabel = Ticket::STATUSES[$oldStatus] ?? $oldStatus;
            $newStatusLabel = Ticket::STATUSES[$newStatus] ?? $newStatus;
            
            TicketMessage::create([
                'request_id' => $ticket->id,
                'sender_type' => 'system',
                'sender_id' => 0,
                'message' => "Статус изменен: {$oldStatusLabel} → {$newStatusLabel}",
                'is_read' => false,
                'created_at' => now(),
            ]);
            
            // Отправляем уведомление пользователю (с контекстом для менеджеров)
            $this->notificationService->send(
                $ticket->user_id,
                'ticket_status_changed',
                'Изменен статус обращения',
                "Статус вашего обращения №{$ticket->request_code} изменен на: {$newStatusLabel}",
                route('tickets.show', $ticket->request_code),
                ['ticket_code' => $ticket->request_code]
            );
        }
        
        // Проверяем назначение админа
        if ($ticket->isDirty('admin_id')) {
            $oldAdminId = $ticket->getOriginal('admin_id');
            $newAdminId = $ticket->admin_id;
            
            if ($newAdminId && !$oldAdminId) {
                // Назначен новый админ
                $admin = User::find($newAdminId);
                $adminName = $admin?->email ?? 'Администратор';
                
                TicketMessage::create([
                    'request_id' => $ticket->id,
                    'sender_type' => 'system',
                    'sender_id' => 0,
                    'message' => "Обращение назначено на {$adminName}",
                    'is_read' => false,
                    'created_at' => now(),
                ]);
                
                // Отправляем уведомление пользователю
                $this->notificationService->send(
                    $ticket->user_id,
                    'ticket_assigned',
                    'Обращение взято в работу',
                    "Ваше обращение №{$ticket->request_code} взято в работу специалистом",
                    route('tickets.show', $ticket->request_code)
                );
            } elseif ($newAdminId && $oldAdminId && $newAdminId !== $oldAdminId) {
                // Переназначение
                $oldAdmin = User::find($oldAdminId);
                $newAdmin = User::find($newAdminId);
                $oldAdminName = $oldAdmin?->email ?? 'Администратор';
                $newAdminName = $newAdmin?->email ?? 'Администратор';
                
                TicketMessage::create([
                    'request_id' => $ticket->id,
                    'sender_type' => 'system',
                    'sender_id' => 0,
                    'message' => "Обращение переназначено: {$oldAdminName} → {$newAdminName}",
                    'is_read' => false,
                    'created_at' => now(),
                ]);
            }
        }
    }
}
