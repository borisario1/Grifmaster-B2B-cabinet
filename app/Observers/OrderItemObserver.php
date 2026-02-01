<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;

class OrderItemObserver
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Handle the OrderItem "created" event.
     */
    public function created(OrderItem $orderItem): void
    {
        $this->updateOrderTotal($orderItem->order);
        $this->logHistory($orderItem->order, 'item_added', "Добавлен товар: {$orderItem->name} ({$orderItem->qty} шт.)");
    }

    /**
     * Handle the OrderItem "updated" event.
     */
    public function updated(OrderItem $orderItem): void
    {
        $this->updateOrderTotal($orderItem->order);
        
        $changes = [];
        if ($orderItem->wasChanged('qty')) {
            $changes[] = "кол-во: {$orderItem->getOriginal('qty')} -> {$orderItem->qty}";
        }
        if ($orderItem->wasChanged('price')) {
            $changes[] = "цена: {$orderItem->getOriginal('price')} -> {$orderItem->price}";
        }

        if (!empty($changes)) {
            $this->logHistory($orderItem->order, 'item_updated', "Изменен товар {$orderItem->name}: " . implode(', ', $changes));
        }
    }

    /**
     * Handle the OrderItem "deleted" event.
     */
    public function deleted(OrderItem $orderItem): void
    {
        $this->updateOrderTotal($orderItem->order);
        $this->logHistory($orderItem->order, 'item_removed', "Удален товар: {$orderItem->name}");
    }

    protected function updateOrderTotal(Order $order): void
    {
        // Пересчитываем сумму заказа
        $total = $order->items()->get()->sum(function ($item) {
            return $item->qty * $item->price;
        });

        // Сохраняем без триггера обсерверов заказа, чтобы не двоить логику, 
        // но нам нужно обновить updated_at
        $order->updateQuietly([
            'total_amount' => $total,
            'updated_at' => now()
        ]);
        
        // Но нам нужно отправить уведомление пользователю, что заказ изменился
        // Поэтому вручную вызовем уведомление, если это админ меняет
        if (Auth::check() && Auth::user()->role === 'admin') {
             $this->notificationService->send(
                $order->user_id,
                'order_updated',
                'Изменение в заказе',
                "В вашем заказе №{$order->order_code} произошли изменения состава.",
                route('orders.show', $order->order_code)
            );
        }
    }

    protected function logHistory(Order $order, string $eventType, string $message): void
    {
        OrderHistory::create([
            'order_id' => $order->id,
            'status_from_id' => $order->status_id, // Статус не меняется
            'status_to_id' => $order->status_id,
            'changed_by_id' => Auth::id(),
            'created_by' => Auth::id(),
            'event_type' => $eventType,
            'message' => $message,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
