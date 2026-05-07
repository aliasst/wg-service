<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryMapping;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Message;
use App\Jobs\SendMessageToBuyer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // Страница выбора категории и отображения покупателей
    public function showBuyers(Request $request)
    {
        $currentAccount = app('currentApiAccount'); // получаем текущий аккаунт из middleware
        if (!$currentAccount) {
            return redirect()->route('admin.dashboard')->with('error', 'Не выбран API-аккаунт');
        }

        $categories = CategoryMapping::where('is_active', true)->get();
        $selectedCategoryId = $request->input('category_id');
        $buyers = collect();

        if ($selectedCategoryId) {
            $category = CategoryMapping::findOrFail($selectedCategoryId);
            $ozonIds = $category->ozon_category_ids;

            $buyers = Order::with('items')
                ->where('api_account_id', $currentAccount->id)   // ← фильтр по аккаунту
                ->where('status', 'delivered')
                ->whereHas('items', function ($q) use ($ozonIds) {
                    $q->whereIn('category_id', $ozonIds);
                })
                ->get();
        }

        return view('admin.messages.buyers', compact('categories', 'selectedCategoryId', 'buyers'));
    }

    // Отправка сообщений выбранным покупателям
    public function send(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:category_mappings,id',
            'header' => 'required|string|max:60',
            'body' => 'required|string|max:1000',
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
        ]);

        $category = CategoryMapping::findOrFail($request->category_id);

        // Сохраняем шаблон сообщения
        $message = Message::create([
            'category_mapping_id' => $category->id,
            'header' => $request->header,
            'body' => $request->body,
            'user_id' => Auth::id(),
        ]);

        $orders = Order::whereIn('id', $request->order_ids)->get();
        foreach ($orders as $order) {
            // Диспатчим джобу для каждого заказа
            dispatch(new SendMessageToBuyer($order, $request->header, $request->body, $message->id));
        }

        return redirect()->route('admin.messages.history')
            ->with('success', 'Сообщения поставлены в очередь отправки.');
    }

    // История отправленных сообщений
    public function history()
    {
        $messages = Message::with('categoryMapping', 'user')
            ->orderBy('id', 'desc')
            ->paginate(20);
        return view('admin.messages.history', compact('messages'));
    }
}
