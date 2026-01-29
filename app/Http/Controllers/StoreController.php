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
        $products = Product::where('is_active', true)
            ->with('details')
            ->orderBy('brand')
            ->get();

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

    // Запрос избранного, страница Избранные товары
    public function wishlist()
    {
        $user = Auth::user();
        $orgId = $user->selected_org_id;

        // 1. Сначала получаем ID товаров, которые УЖЕ в избранном у пользователя
        // Используем ту же таблицу, что и в index()
        $wishlistIds = DB::table('b2b_product_interactions')
            ->where('user_id', $user->id)
            ->where('type', 'wishlist')
            ->pluck('product_id')
            ->toArray();

        // Если избранного нет, можно сразу вернуть пустой результат (опционально)
        if (empty($wishlistIds)) {
            $products = collect(); // Пустая коллекция
        } else {
            // 2. Грузим товары, но ТОЛЬКО те, что в списке $wishlistIds
            $products = Product::where('is_active', true) 
                ->with('details')
                ->whereIn('id', $wishlistIds)
                ->orderBy('brand')
                ->get();
        }

        // --- ДАЛЕЕ КОПИРУЕМ ЛОГИКУ ОБРАБОТКИ ДАННЫХ ИЗ INDEX() ---
        // Это необходимо, чтобы на странице избранного работали кнопки корзины, лайки и цены

        // Получаем айтемы корзины
        $cartItems = CartItem::where('user_id', $user->id)
            ->where('org_id', $orgId)
            ->get()
            ->keyBy('product_id');

        // Получаем лайки (чтобы в избранном можно было лайкнуть товар)
        // Вишлист нам тут по сути не нужен для проверки (они все и так в вишлисте), 
        // но оставим логику получения interactions для унификации, если нужно подсветить лайки.
        $likedIds = DB::table('b2b_product_interactions')
            ->where('user_id', $user->id)
            ->where('type', 'like')
            ->pluck('product_id')
            ->toArray();

        // Справочники для фильтров (строим только по товарам в избранном)
        $brands = $products->pluck('brand')->unique()->filter()->sort()->values();
        $collections = $products->pluck('collection')->unique()->filter()->sort()->values();
        $categories = $products->pluck('product_category')->unique()->filter()->sort()->values();
        $types = $products->pluck('product_type')->unique()->filter()->sort()->values();

        $discounts = DB::table('b2b_discounts')
            ->where('user_id', $user->id)
            ->get()
            ->keyBy(fn($d) => $d->brand ?? 'all');

        // Трансформируем продукты
        $products->transform(function ($item) use ($discounts, $cartItems, $likedIds) {
            $discount = $discounts->get($item->brand) ?? $discounts->get('all');
            $percent = $discount ? $discount->discount_percent : 0;
            
            $item->discount_percent = $percent;
            $item->partner_price = $item->getPartnerPrice($percent);
            
            $inCart = $cartItems->get($item->id);
            $item->in_cart = (bool)$inCart;
            $item->cart_qty = $inCart ? $inCart->qty : 1; 
            
            // Флаги активности
            $item->is_liked = in_array($item->id, $likedIds);
            $item->is_in_wishlist = true; // Тут мы знаем точно, что это страница избранного
            
            return $item;
        });
        
        $cartProductIds = $cartItems->keys()->toArray();

        // Возвращаем ТОТ ЖЕ шаблон, но с дополнительными переменными
        // В будущем будут и другие страницы: Понравилось, Избранное, Покупали ранее и т.д.
        return view('store.index', compact(
            'products', 
            'brands', 
            'collections', 
            'categories', 
            'types', 
            'cartProductIds'
        ))->with([
            'wideLayout' => true,
            'pageTitle' => 'Избранные товары', // Чтобы поменять H1 в шаблоне
            'isWishlist' => true // Флаг для JS/Blade
        ]);
    }

    public function likedProducts()
    {
        $user = Auth::user();
        $orgId = $user->selected_org_id;

        $likedIds = DB::table('b2b_product_interactions')
            ->where('user_id', $user->id)
            ->where('type', 'like')
            ->pluck('product_id')
            ->toArray();

        if (empty($likedIds)) {
            $products = collect();
        } else {
            $products = Product::where('is_active', true)
                ->with('details')
                ->whereIn('id', $likedIds)
                ->orderBy('brand')
                ->get();
        }

        $cartItems = CartItem::where('user_id', $user->id)
            ->where('org_id', $orgId)
            ->get()
            ->keyBy('product_id');

        $wishlistIds = DB::table('b2b_product_interactions')
            ->where('user_id', $user->id)
            ->where('type', 'wishlist')
            ->pluck('product_id')
            ->toArray();

        $brands = $products->pluck('brand')->unique()->filter()->sort()->values();
        $collections = $products->pluck('collection')->unique()->filter()->sort()->values();
        $categories = $products->pluck('product_category')->unique()->filter()->sort()->values();
        $types = $products->pluck('product_type')->unique()->filter()->sort()->values();

        $discounts = DB::table('b2b_discounts')
            ->where('user_id', $user->id)
            ->get()
            ->keyBy(fn($d) => $d->brand ?? 'all');

        $products->transform(function ($item) use ($discounts, $cartItems, $wishlistIds, $likedIds) {
            $discount = $discounts->get($item->brand) ?? $discounts->get('all');
            $percent = $discount ? $discount->discount_percent : 0;

            $item->discount_percent = $percent;
            $item->partner_price = $item->getPartnerPrice($percent);

            $inCart = $cartItems->get($item->id);
            $item->in_cart = (bool)$inCart;
            $item->cart_qty = $inCart ? $inCart->qty : 1;

            $item->is_liked = true;
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
        ))->with([
            'wideLayout' => true,
            'pageTitle' => 'Понравившиеся товары',
            'isLiked' => true
        ]);
    }

    public function orderedProducts()
    {
        $user = Auth::user();
        $orgId = $user->selected_org_id;

        $orderedProductIds = DB::table('b2b_orders')
            ->join('b2b_order_items', 'b2b_orders.id', '=', 'b2b_order_items.order_id')
            ->where('b2b_orders.user_id', $user->id)
            ->distinct()
            ->pluck('b2b_order_items.product_id');

        if ($orderedProductIds->isEmpty()) {
            $products = collect();
        } else {
            $products = Product::where('is_active', true)
                ->with('details')
                ->whereIn('id', $orderedProductIds)
                ->orderBy('brand')
                ->get();
        }

        $cartItems = CartItem::where('user_id', $user->id)
            ->where('org_id', $orgId)
            ->get()
            ->keyBy('product_id');

        $interactions = DB::table('b2b_product_interactions')
            ->where('user_id', $user->id)
            ->whereIn('type', ['like', 'wishlist'])
            ->get()
            ->groupBy('type');

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

        $products->transform(function ($item) use ($discounts, $cartItems, $likedIds, $wishlistIds) {
            $discount = $discounts->get($item->brand) ?? $discounts->get('all');
            $percent = $discount ? $discount->discount_percent : 0;

            $item->discount_percent = $percent;
            $item->partner_price = $item->getPartnerPrice($percent);

            $inCart = $cartItems->get($item->id);
            $item->in_cart = (bool)$inCart;
            $item->cart_qty = $inCart ? $inCart->qty : 1;

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
        ))->with([
            'wideLayout' => true,
            'pageTitle' => 'Ранее заказывали',
            'isOrdered' => true
        ]);
    }

    public function viewedProducts()
    {
        $user = Auth::user();
        $orgId = $user->selected_org_id;

        $viewedProductIds = DB::table('b2b_product_views')
            ->where('user_id', $user->id)
            ->select('product_id')
            ->groupBy('product_id')
            ->orderByRaw('MAX(viewed_at) DESC')
            ->limit(20)
            ->pluck('product_id');

        if ($viewedProductIds->isEmpty()) {
            $products = collect();
        } else {
            $products = Product::where('is_active', true)
                ->with('details')
                ->whereIn('id', $viewedProductIds)
                ->orderByRaw(DB::raw("FIELD(id, " . $viewedProductIds->implode(',') . ")"))
                ->get();
        }

        $cartItems = CartItem::where('user_id', $user->id)
            ->where('org_id', $orgId)
            ->get()
            ->keyBy('product_id');

        $interactions = DB::table('b2b_product_interactions')
            ->where('user_id', $user->id)
            ->whereIn('type', ['like', 'wishlist'])
            ->get()
            ->groupBy('type');

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

        $products->transform(function ($item) use ($discounts, $cartItems, $likedIds, $wishlistIds) {
            $discount = $discounts->get($item->brand) ?? $discounts->get('all');
            $percent = $discount ? $discount->discount_percent : 0;

            $item->discount_percent = $percent;
            $item->partner_price = $item->getPartnerPrice($percent);

            $inCart = $cartItems->get($item->id);
            $item->in_cart = (bool)$inCart;
            $item->cart_qty = $inCart ? $inCart->qty : 1;

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
        ))->with([
            'wideLayout' => true,
            'pageTitle' => 'Недавно смотрели',
            'isViewed' => true
        ]);
    }
}