<?php

/**
 * Название: StoreController.php
 * Дата-время: 28-12-2025 21:55
 * Описание: Контроллер каталога. Отдает все данные для обработки библиотекой List.js.
 */

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $orgId = $user->selected_org_id; 

        $products = Product::orderBy('brand')->get();

        // 1. Получаем айтемы корзины и индексируем их по product_id для быстрого поиска
        $cartItems = CartItem::where('user_id', $user->id)
            ->where('org_id', $orgId)
            ->get()
            ->keyBy('product_id');

        $brands = $products->pluck('brand')->unique()->filter()->sort()->values();
        $collections = $products->pluck('collection')->unique()->filter()->sort()->values();
        $categories = $products->pluck('product_category')->unique()->filter()->sort()->values();
        $types = $products->pluck('product_type')->unique()->filter()->sort()->values();

        $discounts = DB::table('b2b_discounts')
            ->where('user_id', $user->id)
            ->get()
            ->keyBy(fn($d) => $d->brand ?? 'all');

        // 2. Трансформируем продукты
        $products->transform(function ($item) use ($discounts, $cartItems) {
            $discount = $discounts->get($item->brand) ?? $discounts->get('all');
            $percent = $discount ? $discount->discount_percent : 0;
            
            $item->discount_percent = $percent;
            $item->partner_price = $item->getPartnerPrice($percent);
            
            // 3. Проверяем наличие через коллекцию корзины
            $inCart = $cartItems->get($item->id);
            
            $item->in_cart = (bool)$inCart;
            // Если в корзине — берем реальное кол-во, если нет — ставим 1
            $item->cart_qty = $inCart ? $inCart->qty : 1; 
            
            return $item;
        });
        
        // Передаем cartProductIds для List.js (извлекаем ключи из коллекции)
        $cartProductIds = $cartItems->keys()->toArray();

        return view('store.index', compact(
            'products', 
            'brands', 
            'collections', 
            'categories', 
            'types', 
            'cartProductIds'
        ))->with('wideLayout', true);
    }
}