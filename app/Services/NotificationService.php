<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use App\Models\UserNotificationPref;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Создать уведомление и отправить email пользователю, админу и менеджерам.
     * 
     * @param int $userId ID пользователя
     * @param string $eventType Тип события
     * @param string $title Заголовок
     * @param string $message Сообщение
     * @param string|null $linkUrl Ссылка для пользователя
     * @param array|null $context Дополнительные данные (например, order_code для заказов)
     */
    public function send(int $userId, string $eventType, string $title, string $message = '', ?string $linkUrl = null, ?array $context = null): Notification
    {
        // 1. Сохраняем в БД
        $notification = Notification::create([
            'user_id'    => $userId,
            'event_type' => $eventType,
            'title'      => $title,
            'message'    => $message,
            'link_url'   => $linkUrl,
            'is_read'    => false,
        ]);

        // 2. Отправляем Email пользователю (если разрешено настройками)
        if ($this->shouldSendEmail($userId, $eventType)) {
            $this->sendEmailToUser($userId, $title, $message, $linkUrl);
        }

        // 3. Отправляем Email админу (для важных событий)
        if ($this->isAdminEvent($eventType)) {
            $this->sendEmailToAdmin($eventType, $title, $message, $userId);
        }

        // 4. Отправляем Email менеджерам (для заказов и тикетов)
        if ($this->isManagerEvent($eventType)) {
            $this->sendEmailToManagers($eventType, $title, $message, $userId, $context);
        }

        return $notification;
    }

    /**
     * Проверка настроек пользователя.
     */
    protected function shouldSendEmail(int $userId, string $eventType): bool
    {
        // Сообщения в тикетах всегда дублируем на почту
        if ($eventType === 'ticket_message') {
            return true;
        }

        $prefs = UserNotificationPref::where('user_id', $userId)->first();

        // Если настроек нет — по умолчанию всё включено
        if (!$prefs) {
            return true;
        }

        return match ($eventType) {
            'order_created', 'order_status', 'order_status_changed', 'order_updated', 'order_manager_assigned', 'order_comment' => (bool) $prefs->notify_orders,
            'ticket_created', 'ticket_status', 'ticket_closed', 'ticket_message', 'ticket_status_changed', 'ticket_assigned' => (bool) $prefs->notify_ticket,
            'news', 'promo' => (bool) $prefs->notify_news,
            default => (bool) $prefs->notify_general,
        };
    }

    /**
     * Проверить, является ли событие админским (важным).
     */
    protected function isAdminEvent(string $eventType): bool
    {
        $adminEvents = config('b2b.notifications.admin_events', []);
        return in_array($eventType, $adminEvents, true);
    }

    /**
     * Проверить, является ли событие для менеджеров.
     */
    protected function isManagerEvent(string $eventType): bool
    {
        $managerEvents = config('b2b.notifications.manager_events', []);
        return in_array($eventType, $managerEvents, true);
    }

    /**
     * Отправить email пользователю.
     */
    protected function sendEmailToUser(int $userId, string $title, string $message, ?string $linkUrl = null): void
    {
        $user = User::find($userId);
        if (!$user || !$user->email) {
            return;
        }

        $html = $this->buildEmailHtml($title, $message, $linkUrl, $user->name ?? $user->email);
        
        try {
            MailService::send($user->email, $title, $html);
        } catch (\Exception $e) {
            Log::error("Email sending to user failed: " . $e->getMessage());
        }
    }

    /**
     * Отправить email администратору.
     */
    protected function sendEmailToAdmin(string $eventType, string $title, string $message, int $userId): void
    {
        $adminEmail = config('b2b.notifications.admin_email');
        if (!$adminEmail) {
            return;
        }

        $user = User::find($userId);
        $userInfo = $user ? "Пользователь: {$user->name} ({$user->email})" : "User ID: {$userId}";
        
        $adminMessage = "{$message}\n\n{$userInfo}\nТип события: {$eventType}";
        $html = $this->buildEmailHtml("[ADMIN] {$title}", $adminMessage, null, 'Администратор');

        try {
            MailService::send($adminEmail, "[ADMIN] {$title}", $html);
        } catch (\Exception $e) {
            Log::error("Email sending to admin failed: " . $e->getMessage());
        }
    }

    /**
     * Отправить email менеджерам.
     */
    protected function sendEmailToManagers(string $eventType, string $title, string $message, int $userId, ?array $context = null): void
    {
        $managerEmails = config('b2b.notifications.manager_email');
        if (!$managerEmails) {
            return;
        }

        // Поддержка нескольких email через запятую
        $emails = array_map('trim', explode(',', $managerEmails));

        $user = User::find($userId);
        $userInfo = $user ? "Клиент: {$user->name} ({$user->email})" : "User ID: {$userId}";
        if ($user && $user->phone) {
            $userInfo .= "\nТелефон: {$user->phone}";
        }
        
        // Формируем расширенное сообщение для заказов
        $managerMessage = $message;
        $adminLink = null;
        
        if (!empty($context['order_code'])) {
            $orderCode = $context['order_code'];
            $adminLink = url("/management/orders/{$orderCode}/view");
            
            // Загружаем заказ с позициями
            $order = Order::with('items')->where('order_code', $orderCode)->first();
            
            if ($order) {
                $managerMessage = "Заказ №{$orderCode}\n";
                $managerMessage .= "Сумма: " . number_format($order->total_amount, 2, '.', ' ') . " ₽\n";
                $managerMessage .= "Позиций: {$order->total_items}\n\n";
                
                // Добавляем информацию о товарах
                if ($order->items->count() > 0) {
                    $managerMessage .= "Состав заказа:\n";
                    foreach ($order->items as $item) {
                        $itemTotal = $item->qty * $item->price;
                        $managerMessage .= "• {$item->name} — {$item->qty} шт. × " . number_format($item->price, 2, '.', ' ') . " ₽ = " . number_format($itemTotal, 2, '.', ' ') . " ₽\n";
                    }
                }
                
                // Организация
                if ($order->org_name) {
                    $userInfo .= "\nОрганизация: {$order->org_name}";
                    if ($order->org_inn) {
                        $userInfo .= " (ИНН: {$order->org_inn})";
                    }
                }
            }
        }
        
        // Обработка контекста тикета
        if (!empty($context['ticket_code'])) {
            $ticketCode = $context['ticket_code'];
            $adminLink = url("/management/tickets/{$ticketCode}/edit");
            
            // Загружаем тикет с сообщениями и вложениями
            $ticket = \App\Models\Ticket::with('messages.attachments')->where('request_code', $ticketCode)->first();
            
            if ($ticket) {
                $statusLabel = \App\Models\Ticket::STATUSES[$ticket->status] ?? $ticket->status;
                $categoryLabel = \App\Models\Ticket::CATEGORIES[$ticket->category] ?? $ticket->category;
                
                $managerMessage = "Обращение №{$ticketCode}\n";
                $managerMessage .= "Тема: {$ticket->topic}\n";
                $managerMessage .= "Статус: {$statusLabel}\n";
                $managerMessage .= "Категория: {$categoryLabel}\n\n";
                
                // Последнее сообщение
                $lastMessage = $ticket->messages->last();
                if ($lastMessage) {
                    $msgPreview = mb_substr($lastMessage->message, 0, 200);
                    if (mb_strlen($lastMessage->message) > 200) {
                        $msgPreview .= '...';
                    }
                    $managerMessage .= "Сообщение:\n{$msgPreview}\n";
                    
                    // Информация о вложениях
                    $attachmentsCount = $lastMessage->attachments->count();
                    if ($attachmentsCount > 0) {
                        $managerMessage .= "\n📎 Прикреплено файлов: {$attachmentsCount} (доступны в админке)\n";
                    }
                }
                
                // Организация
                if ($ticket->org_name) {
                    $userInfo .= "\nОрганизация: {$ticket->org_name}";
                    if ($ticket->org_inn) {
                        $userInfo .= " (ИНН: {$ticket->org_inn})";
                    }
                }
            }
        }
        
        $managerMessage .= "\n\n{$userInfo}";
        $html = $this->buildEmailHtml($title, $managerMessage, $adminLink, 'Менеджер');

        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            
            try {
                MailService::send($email, "[B2B] {$title}", $html);
            } catch (\Exception $e) {
                Log::error("Email sending to manager {$email} failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Собрать HTML письма.
     */
    protected function buildEmailHtml(string $title, string $message, ?string $linkUrl, string $recipientName): string
    {
        $linkHtml = $linkUrl 
            ? "<p><a href=\"{$linkUrl}\" style=\"display:inline-block;padding:12px 24px;background:#2563eb;color:#fff;text-decoration:none;border-radius:6px;\">Перейти к деталям</a></p>" 
            : '';

        $messageHtml = nl2br(htmlspecialchars($message));

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{$title}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); padding: 20px; border-radius: 8px 8px 0 0;">
        <h1 style="color: #fff; margin: 0; font-size: 24px;">Grifmaster B2B</h1>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px;">
        <p style="color: #666; margin-bottom: 20px;">Здравствуйте, {$recipientName}!</p>
        <h2 style="color: #1e3a8a; margin-bottom: 15px;">{$title}</h2>
        <div style="background: #f8fafc; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
            <p style="margin: 0;">{$messageHtml}</p>
        </div>
        {$linkHtml}
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
        <p style="color: #9ca3af; font-size: 12px; margin: 0;">
            Это автоматическое уведомление от системы Grifmaster B2B.<br>
            Пожалуйста, не отвечайте на это письмо.
        </p>
    </div>
</body>
</html>
HTML;
    }
    
    public function markAsRead(int $userId, int $notificationId): void
    {
        Notification::where('user_id', $userId)
            ->where('id', $notificationId)
            ->update(['is_read' => true]);
    }

    public function markAllAsRead(int $userId): void
    {
        Notification::where('user_id', $userId)->update(['is_read' => true]);
    }
}