<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\CategoryMapping;

class ProductsController extends Controller
{
    public function index()
    {
        // Уникальные товары (группировка по sku)
        $products = OrderItem::select('sku', 'offer_id', 'product_name', 'category_id')
            ->groupBy('sku', 'offer_id', 'product_name', 'category_id')
            ->orderBy('product_name')
            ->get();

        // Получаем все активные внутренние категории
        $internalCategories = CategoryMapping::where('is_active', true)->get();

        // Для каждого товара находим соответствующую внутреннюю категорию (первую)
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
