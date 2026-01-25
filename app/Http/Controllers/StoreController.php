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

        // Грузим товары с деталями
        $products = Product::with('details')->orderBy('brand')->get();

        // 1. Получаем айтемы корзины
        $cartItems = CartItem::where('user_id', $user->id)
            ->where('org_id', $orgId)
            ->get()
            ->keyBy('product_id');

        // 2. ПОЛУЧАЕМ СПИСОК ЛАЙКОВ И ВИШЛИСТА ТЕКУЩЕГО ЮЗЕРА
        $interactions = DB::table('b2b_product_interactions')
            ->where('user_id', $user->id)
            ->get()
            ->groupBy('type'); // Группируем по типу: 'like', 'wishlist'

        // Массивы ID товаров
        $likedIds = isset($interactions['like']) ? $interactions['like']->pluck('product_id')->toArray() : [];
        $wishlistIds = isset($interactions['wishlist']) ? $interactions['wishlist']->pluck('product_id')->toArray() : [];

        $brands = $products->pluck('brand')->unique()->filter()->sort()->values();
        $collections = $products->pluck('collection')->unique()->filter()->sort()->values();
        $categories = $products->pluck('product_category')->unique()->filter()->sort()->values();
        $types = $products->pluck('product_type')->unique()->filter()->sort()->values();

        $discounts = DB::table('b2b_discounts')
            ->where('user_id', $user->id)
            ->get()
            ->keyBy(fn($d) => $d->brand ?? 'all');

        // 3. Трансформируем продукты
        $products->transform(function ($item) use ($discounts, $cartItems, $likedIds, $wishlistIds) {
            $discount = $discounts->get($item->brand) ?? $discounts->get('all');
            $percent = $discount ? $discount->discount_percent : 0;
            
            $item->discount_percent = $percent;
            $item->partner_price = $item->getPartnerPrice($percent);
            
            $inCart = $cartItems->get($item->id);
            $item->in_cart = (bool)$inCart;
            $item->cart_qty = $inCart ? $inCart->qty : 1; 
            
            // Проставляем флаги активности
            $item->is_liked = in_array($item->id, $likedIds);
            $item->is_in_wishlist = in_array($item->id, $wishlistIds);
            
            return $item;
        });
        
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