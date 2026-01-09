<?php

/**
 * Название: OrderController
 * Дата-время: 08-01-2026 19:45
 * Описание: Контроллер для работы с заказами в B2B-кабинете. 
 * Реализует вывод списка заказов с расширенными данными плательщика 
 * и детальный просмотр состава заказа с историей изменений.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Список всех заказов пользователя + статистика текущей корзины
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Загружаем заказы с джоинами профиля и организаций
        $orders = DB::table('b2b_orders as o')
            ->leftJoin('b2b_organizations as org', 'org.id', '=', 'o.org_id')
            ->leftJoin('b2b_user_profile as p', 'p.user_id', '=', 'o.user_id')
            // Подзапрос для получения даты последнего события из истории
            ->leftJoin(DB::raw('(SELECT order_id, MAX(created_at) as last_act FROM b2b_order_history GROUP BY order_id) as h'), 'h.order_id', '=', 'o.id')
            ->where('o.user_id', $user->id)
            ->select(
                'o.*', 
                'org.name as org_name', 
                'org.inn as org_inn',
                'p.work_phone as user_phone', // Используем work_phone из миграции
                DB::raw("CONCAT(COALESCE(p.first_name, ''), ' ', COALESCE(p.last_name, '')) as user_full_name"),
                'h.last_act as last_activity_at'
            )
            ->orderBy('o.id', 'desc')
            ->paginate(15);

        // 2. Считаем статистику незавершенного заказа для ТЕКУЩЕЙ организации
        // Это нужно для блока-напоминания в шаблоне
        $currentCartStats = DB::table('b2b_cart_items')
            ->where('user_id', $user->id)
            ->where('org_id', $user->selected_org_id)
            ->selectRaw('COUNT(*) as count, SUM(qty) as total_qty')
            ->first();
        // Получаем данные текущей организации
        $currentOrg = $user->selected_org_id 
            ? DB::table('b2b_organizations')->where('id', $user->selected_org_id)->first() 
            : null;

        return view('orders.index', compact('orders', 'currentCartStats', 'currentOrg'));

    }

    /**
     * Просмотр деталей конкретного заказа
     */
    public function show($code)
    {
        $user = Auth::user();

        // 1. Загружаем заказ с проверкой владельца
        $order = DB::table('b2b_orders')
            ->where('order_code', $code)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            abort(404);
        }

        // 2. Получаем состав заказа (позиции)
        $items = DB::table('b2b_order_items')
            ->where('order_id', $order->id)
            ->get();

        // 3. Получаем историю событий (статусы, комментарии)
        $history = DB::table('b2b_order_history')
            ->where('order_id', $order->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('orders.show', compact('order', 'items', 'history'));
    }
}