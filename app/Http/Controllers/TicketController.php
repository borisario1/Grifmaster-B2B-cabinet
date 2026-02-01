<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function index()
    {
        $tickets = Ticket::where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        $categories = Ticket::CATEGORIES;
        return view('tickets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'topic'   => 'required|string|max:255',
            'message' => 'required|string',
            'category'=> 'required|string',
        ]);

        try {
            $ticket = $this->ticketService->createTicket(Auth::user(), $request->all());
            return redirect()->route('tickets.show', $ticket->request_code)
                             ->with('success', 'Обращение успешно создано');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function show($code)
    {
        $ticket = Ticket::where('request_code', $code)
            ->where('user_id', Auth::id())
            ->with('messages.attachments') // Жадная загрузка сообщений с вложениями
            ->firstOrFail();

        // Помечаем сообщения от поддержки как прочитанные
        $ticket->messages()
            ->where('sender_type', '!=', 'user')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('tickets.show', compact('ticket'));
    }

    public function sendMessage(Request $request, $code)
    {
        $ticket = Ticket::where('request_code', $code)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'message' => 'required|string',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:102400', // 100MB per file
        ]);

        // Проверяем суммарный размер
        $files = $request->file('attachments', []);
        $totalSize = collect($files)->sum(fn($f) => $f->getSize());
        
        if ($totalSize > 104857600) { // 100MB total
            return back()->withErrors(['attachments' => 'Суммарный размер файлов не может превышать 100 МБ']);
        }

        $this->ticketService->addMessage($ticket, Auth::user(), $request->input('message'), $files);

        return back()->with('success', 'Сообщение отправлено');
    }

    public function close($code)
    {
        $ticket = Ticket::where('request_code', $code)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        try {
            $this->ticketService->closeTicket(Auth::user(), $ticket);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back();
    }

    /**
     * Скачивание вложения с проверкой прав доступа
     */
    public function downloadAttachment($id)
    {
        $attachment = TicketAttachment::with('message.ticket')->findOrFail($id);
        $ticket = $attachment->message->ticket ?? null;
        
        if (!$ticket) {
            abort(404);
        }

        // Проверяем права: владелец тикета или админ
        $user = Auth::user();
        if ($ticket->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Нет доступа к этому файлу');
        }

        if (!Storage::disk('private')->exists($attachment->file_path)) {
            abort(404, 'Файл не найден');
        }

        return Storage::disk('private')->download(
            $attachment->file_path,
            $attachment->original_name
        );
    }
}
