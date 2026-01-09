<?php

namespace App\Services;

use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;

class CartService
{
    /**
     * Получить сводку корзины для текущего пользователя и выбранной организации
     * Аналог getSummary из легаси
     */
    public function getSummary(): array
    {
        $user = Auth::user();
        $orgId = $user->selected_org_id; // Используем выбранную в профиле организацию

        $items = CartItem::where('user_id', $user->id)
            ->where('org_id', $orgId)
            ->with('product')
            ->get();

        $totalQty = $items->sum('qty');
        $totalPositions = $items->count();
        
        // Пока считаем по РРЦ (price из b2b_products)
        $totalAmount = $items->reduce(function ($carry, $item) {
            return $carry + ($item->qty * ($item->product->price ?? 0));
        }, 0);

        return [
            'qty'    => $totalQty,
            'pos'    => $totalPositions,
            'amount' => (float)$totalAmount,
        ];
    }
}