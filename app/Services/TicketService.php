<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Exception;

class TicketService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Создание нового тикета (логика из Requests::create)
     */
    public function createTicket(User $user, array $data): Ticket
    {
        if (empty($data['topic']) || empty($data['message'])) {
            throw new Exception("Тема и сообщение обязательны.");
        }

        // 1. Проверка организации
        $orgId = $user->selected_org_id;
        
        // Если у юзера есть организации, но ни одна не выбрана
        if ($user->organizations()->exists() && empty($orgId)) {
            throw new Exception("Пожалуйста, выберите активную организацию перед созданием обращения.");
        }

        // 2. Получаем данные организации для снапшота
        $orgData = [
            'org_id' => null, 'org_name' => null, 
            'org_inn' => null, 'org_kpp' => null, 'org_ogrn' => null
        ];

        if ($orgId) {
            $org = Organization::find($orgId);
            if ($org) {
                $orgData = [
                    'org_id'   => $org->id,
                    'org_name' => $org->name,
                    'org_inn'  => $org->inn,
                    'org_kpp'  => $org->kpp,
                    'org_ogrn' => $org->ogrn,
                ];
            }
        }

        // 3. Телефон (приоритет: профиль -> юзер)
        $phone = $user->profile->work_phone ?? $user->phone;

        return DB::transaction(function () use ($user, $data, $orgData, $phone) {
            // Генерируем код
            $code = $this->generateRequestCode($user->id, $orgData['org_id']);

            // Создаем тикет
            $ticket = Ticket::create(array_merge([
                'user_id'    => $user->id,
                'user_email' => $user->email,
                'user_phone' => $phone,
                'category'   => $data['category'] ?? 'general',
                'topic'      => $data['topic'],
                'status'     => 'open',
                'request_code' => $code,
            ], $orgData));

            // Создаем первое сообщение
            $this->addMessage($ticket, $user, $data['message']);

            // Отправляем уведомление (внутреннее + email)
            $this->notificationService->send(
                $user->id,
                'ticket_created',
                "Создано новое обращение",
                "Ваше обращение №{$code} зарегистрировано.",
                route('tickets.show', $code) // Предполагаем наличие роута
            );

            return $ticket;
        });
    }

    /**
     * Добавление сообщения в тикет
     */
    public function addMessage(Ticket $ticket, User $user, string $messageText): TicketMessage
    {
        return TicketMessage::create([
            'request_id'  => $ticket->id,
            'sender_type' => 'user',
            'sender_id'   => $user->id,
            'message'     => $messageText,
            'is_read'     => false, // Для админа оно непрочитано
        ]);
    }

    /**
     * Закрытие тикета пользователем
     */
    public function closeTicket(User $user, Ticket $ticket): void
    {
        if ($ticket->user_id !== $user->id) {
            throw new Exception("Вы не можете закрыть чужое обращение.");
        }

        DB::transaction(function () use ($user, $ticket) {
            $ticket->update(['status' => 'closed']);

            // Добавляем системное сообщение в историю чата
            TicketMessage::create([
                'request_id'  => $ticket->id,
                'sender_type' => 'system',
                'sender_id'   => $user->id,
                'message'     => "Обращение закрыто пользователем.",
                'is_read'     => true,
            ]);

            $this->notificationService->send(
                $user->id,
                'ticket_closed',
                "Обращение закрыто",
                "Вы закрыли обращение №{$ticket->request_code}.",
                route('tickets.show', $ticket->request_code)
            );
        });

        // Принудительно добавляем flash-сообщение для вывода в шаблоне
        session()->flash('success', 'Обращение успешно закрыто.');
    }

    /**
     * Генерация кода обращения (Uxxxx-REQyyy или Rxxxx-REQyyy)
     */
    protected function generateRequestCode(int $userId, ?int $orgId): string
    {
        $date = date('dmy');
        
        if ($orgId) {
            $prefix = 'R';
            $entityId = str_pad((string)$orgId, 4, "0", STR_PAD_LEFT);
        } else {
            $prefix = 'U';
            $entityId = str_pad((string)$userId, 4, "0", STR_PAD_LEFT);
        }

        // Считаем кол-во заявок пользователя для порядкового номера
        $count = Ticket::where('user_id', $userId)->count();
        $num = str_pad((string)($count + 1), 3, "0", STR_PAD_LEFT);

        return "{$date}-{$prefix}{$entityId}-REQ{$num}";
    }
}
