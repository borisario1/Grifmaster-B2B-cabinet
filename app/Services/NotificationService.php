<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\UserNotificationPref;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Создать уведомление и отправить email, если разрешено настройками.
     */
    public function send(int $userId, string $eventType, string $title, string $message = '', ?string $linkUrl = null): Notification
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

        // 2. Проверяем настройки и отправляем Email
        if ($this->shouldSendEmail($userId, $eventType)) {
            $this->sendEmail($userId, $title, $message);
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
            'order_created', 'order_status', 'order_comment' => (bool) $prefs->notify_orders,
            'ticket_created', 'ticket_status', 'ticket_closed' => (bool) $prefs->notify_ticket,
            'news', 'promo' => (bool) $prefs->notify_news,
            default => (bool) $prefs->notify_general,
        };
    }

    protected function sendEmail(int $userId, string $title, string $message): void
    {
        $user = User::find($userId);
        if (!$user || !$user->email) {
            return;
        }

        try {
            // Простая отправка HTML письма
            Mail::html("<h2>{$title}</h2><p>{$message}</p>", function ($m) use ($user, $title) {
                $m->to($user->email)->subject($title);
            });
        } catch (\Exception $e) {
            Log::error("Email sending failed: " . $e->getMessage());
        }
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