<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class OrderController extends Controller
{
    public function sync(Request $request)
    {
        $apiAccount = app('currentApiAccount');
        if (!$apiAccount) {
            return redirect()->back()->with('error', 'Нет активного API-аккаунта');
        }

        $userId = auth()->id();
        $days = $request->input('days', 30);

        // Запускаем команду в фоне (через очередь, но пока синхронно)
        Artisan::call("ozon:sync", [
            '--days' => $days,
            '--user_id' => $userId,
            '--api_account_id' => $apiAccount->id,
        ]);

        $output = Artisan::output();

        return redirect()->back()->with('success', 'Синхронизация запущена. Результат: ' . $output);
    }
}
