<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            ->with('messages') // Жадная загрузка сообщений
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

        $request->validate(['message' => 'required|string']);

        $this->ticketService->addMessage($ticket, Auth::user(), $request->input('message'));

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
}
