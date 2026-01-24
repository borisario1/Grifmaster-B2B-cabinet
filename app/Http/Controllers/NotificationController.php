<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $user = Auth::user();
        // Используем пагинацию Laravel вместо ручного offset из легаси
        $notifications = $user->notifications() // отношение hasMany в модели User
                              ->orderBy('id', 'desc')
                              ->paginate(20);

        // Считаем количество непрочитанных для отображения кнопки
        $unreadCount = $user->notifications()
                            ->where('is_read', false)
                            ->count();
        
        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markRead(Request $request, $id)
    {
        $this->notificationService->markAsRead(Auth::id(), (int)$id);
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        return back();
    }

    public function markAllRead(Request $request)
    {
        $this->notificationService->markAllAsRead(Auth::id());
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Все уведомления помечены прочитанными');
    }
}
