<?php

/**
 * Название: CartController.php
 * Дата-время: 08-01-2026 15:50
 * Описание: Контроллер корзины.
 * Отвечает за добавление товаров в корзину и обновление данных корзины.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function add(Request $request, CartService $cartService)
    {
        $request->validate([
            'product_id' => 'required|exists:b2b_products,id',
            'qty' => 'required|integer|min:0'
        ]);

        $user = Auth::user();
        $product = Product::findOrFail($request->product_id);
        $qty_input = (int)$request->qty;
        $mode = $request->input('mode', 'add'); // 'add' для каталога, 'set' для корзины

        $cartItem = CartItem::where([
            'user_id' => $user->id,
            'org_id' => $user->selected_org_id,
            'product_id' => $request->product_id,
        ])->first();

        $was_in_cart = (bool)$cartItem;
        $action = '';

        if ($qty_input <= 0) {
            if ($was_in_cart) {
                $cartItem->delete();
                $action = 'removed';
            } else {
                $action = 'not_found';
            }
        } else {
            if ($was_in_cart) {
                // Если 'set' — перезаписываем, если 'add' — прибавляем
                $cartItem->qty = ($mode === 'set') ? $qty_input : ($cartItem->qty + $qty_input);
                $cartItem->save();
                $action = ($mode === 'set') ? 'updated' : 'increased';
            } else {
                $cartItem = CartItem::create([
                    'user_id' => $user->id,
                    'org_id' => $user->selected_org_id,
                    'product_id' => $request->product_id,
                    'qty' => $qty_input
                ]);
                $action = 'added';
            }
        }

        $summary = $cartService->getSummary();
        return response()->json([
            'success' => true,
            'action' => $action,
            'product_name' => $product->name,
            'added_qty' => $qty_input,
            'total_qty' => $cartItem->qty ?? 0,
            'summary' => array_merge($summary, [
                'amount_formatted' => number_format($summary['amount'], 2, '.', ' ')
            ])
        ]);
    }

    // Метод для очистки корзины
    public function clear(Request $request)
    {
        $user = Auth::user();
        
        // Очищаем только корзину текущего пользователя и текущей организации
        DB::table('b2b_cart_items')
            ->where('user_id', $user->id)
            ->where('org_id', $user->selected_org_id)
            ->delete();

        // Если запрос пришел через AJAX (например, из корзины)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true, 
                'message' => 'Корзина очищена'
            ]);
        }

        // Если это обычная отправка формы (как напр. из списка заказов)
        // Добавляем 'ok' во флеш-сессию, чтобы сработал тост/алерт
        return redirect()->back()->with('ok', 'Ваш незавершенный заказ успешно удален');
    }
    
    public function checkout(CartService $cartService)
    {
        $user = Auth::user();
        $orgId = $user->selected_org_id;

        $cartItems = CartItem::where('user_id', $user->id)
            ->where('org_id', $orgId)
            ->with('product')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Корзина пуста');
        }

        return DB::transaction(function () use ($user, $orgId, $cartItems, $cartService) {
            // 1. Сбор данных организации
            $org = DB::table('b2b_organizations')->where('id', $orgId)->first();
            
            // 2. Генерация номера заказа по твоей формуле
            $date = now()->format('dmy');
            $prefix = $orgId ? 'R' : 'U';
            $entityId = str_pad($orgId ?? $user->id, 4, "0", STR_PAD_LEFT);
            
            // Считаем количество заказов этой сущности для порядкового номера
            $count = DB::table('b2b_orders')
                ->where($orgId ? 'org_id' : 'user_id', $orgId ?? $user->id)
                ->when(!$orgId, fn($q) => $q->whereNull('org_id'))
                ->count();
            $ordNum = str_pad($count + 1, 4, "0", STR_PAD_LEFT);

            $orderCode = "{$date}-{$prefix}{$entityId}-{$ordNum}";

            $summary = $cartService->getSummary();

            // 3. Создание заказа
            $orderId = DB::table('b2b_orders')->insertGetId([
                'order_code'   => $orderCode,
                'user_id'      => $user->id,
                'org_id'       => $orgId,
                'org_name'     => $org->name ?? null,
                'org_inn'      => $org->inn ?? null,
                'org_kpp'      => $org->kpp ?? null,
                'total_items'  => $summary['pos'],
                'total_amount' => $summary['amount'],
                'status'       => 'new',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // 4. Перенос позиций
            foreach ($cartItems as $item) {
                DB::table('b2b_order_items')->insert([
                    'order_id'   => $orderId,
                    'product_id' => $item->product_id,
                    'article'    => $item->product->article,
                    'name'       => $item->product->name,
                    'qty'        => $item->qty,
                    'price'      => $item->product->price,
                    //'image'      => $item->product->image_url,
                    'created_at' => now(),
                ]);
            }

            // 5. История заказа (Legacy style)
            DB::table('b2b_order_history')->insert([
                'order_id'   => $orderId,
                'event_type' => 'created',
                'message'    => "Заказ создан. Сумма: {$summary['amount']} ₽, позиций: {$summary['pos']}",
                'status_to'  => 'new',
                'created_by' => $user->id,
                'created_at' => now(),
            ]);

            // 6. Очистка корзины
            CartItem::where('user_id', $user->id)->where('org_id', $orgId)->delete();

            // Редирект на страницу успеха
            return redirect()->route('cart.success', ['code' => $orderCode]);
        });
    }

    public function index(CartService $cartService)
    {
        $user = auth()->user();
        
        // Загружаем товары корзины вместе с данными о продуктах
        $items = \App\Models\CartItem::where('user_id', $user->id)
            ->where('org_id', $user->selected_org_id)
            ->with('product')
            ->get();

        $summary = $cartService->getSummary();
        
        // Организации
        $organizations = \DB::table('b2b_organizations')->where('user_id', $user->id)->get();
        $currentOrg = $organizations->where('id', $user->selected_org_id)->first();
        
        // Профиль (для физлица)
        $profile = \DB::table('b2b_user_profile')->where('user_id', $user->id)->first();

        return view('store.cart', compact('items', 'summary', 'organizations', 'currentOrg', 'profile', 'user'));
    }

    public function success($code)
    {
        // Проверяем, что заказ принадлежит пользователю
        $order = DB::table('b2b_orders')
            ->where('order_code', $code)
            ->where('user_id', Auth::id())
            ->first();

        if (!$order) abort(404);

        return view('store.success', compact('order'));
    }

}