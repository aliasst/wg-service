<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\CategoryMapping;

class ProductsController extends Controller
{
    public function index()
    {
        $currentAccount = app('currentApiAccount');
        if (!$currentAccount) {
            return redirect()->route('admin.dashboard')->with('error', 'Не выбран API-аккаунт');
        }

        // Уникальные товары только для текущего аккаунта
        $products = OrderItem::select('order_items.sku', 'order_items.offer_id', 'order_items.product_name', 'order_items.category_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.api_account_id', $currentAccount->id)
            ->groupBy('order_items.sku', 'order_items.offer_id', 'order_items.product_name', 'order_items.category_id')
            ->orderBy('order_items.product_name')
            ->get();

        // Получаем все активные внутренние категории
        $internalCategories = CategoryMapping::where('is_active', true)->get();

        // Для каждого товара находим соответствующую внутреннюю категорию
        foreach ($products as $product) {
            $product->internal_name = null;
            if ($product->category_id) {
                foreach ($internalCategories as $cat) {
                    if (in_array($product->category_id, $cat->ozon_category_ids)) {
                        $product->internal_name = $cat->internal_name;
                        break;
                    }
                }
            }
        }

        return view('admin.products.index', compact('products'));
    }
}
