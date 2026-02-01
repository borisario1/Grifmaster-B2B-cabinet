<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderHistory;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class OrderObserver
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Handle the Order "updating" event.
     */
    public function updating(Order $order): void
    {
        if ($order->isDirty('status_id')) {
            $order->last_status_change_at = now();
            
            // Синхронизируем легаси поле status
            // Загружаем связь, если она еще не загружена
            $status = $order->orderStatus ?? \App\Models\OrderStatus::find($order->status_id);
            if ($status) {
                $order->status = $status->name;
            }

            // Если новый статус финальный (закрыт/отменен)
            if ($status && $status->is_final) {
                // Если дата закрытия еще не установлена - ставим
                if (!$order->closed_at) {
                    $order->closed_at = now();
                }
            } else {
                // Если вернули в работу - сбрасываем дату закрытия
                $order->closed_at = null;
            }
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // 1. Логирование истории изменений статуса
        if ($order->wasChanged('status_id')) {
            $originalStatusId = $order->getOriginal('status_id');
            $newStatusId = $order->status_id;

            OrderHistory::create([
                'order_id' => $order->id,
                'status_from_id' => $originalStatusId,
                'status_to_id' => $newStatusId,
                'changed_by_id' => Auth::id() ?? $order->admin_id, // Текущий юзер или админ заказа (если авто)
                'created_by' => Auth::id() ?? $order->admin_id, // Add this line
                'event_type' => 'status_change',
                'message' => $order->closure_comment ?? '', // Используем пустую строку, если коммент пустой
            ]);

            // Уведомление пользователю (с контекстом для менеджеров)
            $statusLabel = $order->orderStatus->label ?? 'Неизвестно';
            
            $this->notificationService->send(
                $order->user_id,
                'order_status_changed',
                'Статус заказа изменен',
                "Статус заказа №{$order->order_code} изменен на: {$statusLabel}",
                route('orders.show', $order->order_code),
                ['order_code' => $order->order_code]
            );
        }

        // 2. Уведомление о назначении менеджера
        if ($order->wasChanged('admin_id') && $order->admin_id) {
            $this->notificationService->send(
                $order->user_id,
                'order_manager_assigned',
                'Назначен персональный менеджер',
                "За вашим заказом №{$order->order_code} закреплен персональный менеджер.",
                route('orders.show', $order->order_code)
            );
        }
    }
}
