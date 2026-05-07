<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiAccount;
use App\Models\Order;
use App\Models\Message;
use App\Models\MessageLog;
use App\Services\OzonApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $currentAccount = app('currentApiAccount');
        if (!$currentAccount) {
            return view('admin.dashboard', ['error' => 'Не выбран API-аккаунт']);
        }

        // Статистика заказов (только для текущего аккаунта)
        $totalOrders = Order::where('api_account_id', $currentAccount->id)->count();
        $ordersLast7Days = Order::where('api_account_id', $currentAccount->id)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();
        $deliveredOrders = Order::where('api_account_id', $currentAccount->id)
            ->where('status', 'delivered')
            ->count();

        // Статистика сообщений
        $totalMessages = Message::count(); // общие шаблоны (пока общие)
        $logs = MessageLog::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
        $successCount = $logs['success'] ?? 0;
        $errorCount = $logs['error'] ?? 0;

        // Последние 5 логов отправки (для таблицы)
        $recentLogs = MessageLog::with('order')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Дата последней синхронизации (максимальная created_at в orders)
        $lastSync = Order::where('api_account_id', $currentAccount->id)
            ->max('created_at');

        return view('admin.dashboard', compact(
            'currentAccount',
            'totalOrders',
            'ordersLast7Days',
            'deliveredOrders',
            'totalMessages',
            'successCount',
            'errorCount',
            'recentLogs',
            'lastSync'
        ));
    }

    public function checkOzon()
    {
        $currentAccount = app('currentApiAccount');
        if (!$currentAccount) {
            return response()->json(['status' => 'error', 'message' => 'Нет активного API-аккаунта']);
        }
        try {
            $api = new OzonApiService($currentAccount->client_id, $currentAccount->api_key);
            $connected = $api->checkConnection();
            return response()->json([
                'status' => $connected ? 'success' : 'error',
                'message' => $connected ? 'Соединение с API Ozon установлено' : 'Ошибка соединения. Проверьте ключи.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
