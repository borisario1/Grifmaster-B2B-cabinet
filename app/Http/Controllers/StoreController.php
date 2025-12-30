<?php

/**
 * Название: StoreController.php
 * Дата-время: 28-12-2025 21:55
 * Описание: Контроллер каталога. Отдает все данные для обработки библиотекой List.js.
 */

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    public function index()
    {
        $user = \Auth::user();
        $products = \App\Models\Product::orderBy('brand')->get();

        // Списки для фильтров
        $brands = $products->pluck('brand')->unique()->filter()->sort()->values();
        $collections = $products->pluck('collection')->unique()->filter()->sort()->values();
        $categories = $products->pluck('product_category')->unique()->filter()->sort()->values();
        $types = $products->pluck('product_type')->unique()->filter()->sort()->values();

        // Скидки
        $discounts = \DB::table('b2b_discounts')->where('user_id', $user->id)->get()->keyBy(fn($d) => $d->brand ?? 'all');

        $products->transform(function ($item) use ($discounts) {
            $discount = $discounts->get($item->brand) ?? $discounts->get('all');
            $percent = $discount ? $discount->discount_percent : 0;
            $item->discount_percent = $percent;
            $item->partner_price = $item->getPartnerPrice($percent);
            return $item;
        });
        
        $view = view('store.index', compact('products', 'brands', 'collections', 'categories', 'types'))->with('wideLayout', true)->render();

        return response($view)
            ->header('Content-Length', strlen($view));
    }
}