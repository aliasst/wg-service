<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiAccount;
use App\Services\OzonApiService;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class TestController extends Controller
{
    public function index()
    {
        // 1. Определяем api_account_id
        $clientId = env('OZON_CLIENT_ID');
        $apiKey = env('OZON_API_KEY');


        echo "<pre>";
        echo "<h1>Тестирование Ozon API (сырые ответы)</h1>";

        try {
            $api = new OzonApiService($clientId, $apiKey);
            echo "<strong>✅ Сервис инициализирован</strong>\n\n";
        } catch (\Exception $e) {
            echo "<strong style='color:red'>❌ Ошибка инициализации: " . $e->getMessage() . "</strong>";
            echo "</pre>";
            return;
        }

        // 1. Проверка соединения (checkConnection)
        echo "<h2>1. checkConnection()</h2>";
        try {
            $result = $api->checkConnection();
            echo "Результат (bool): " . ($result ? 'true' : 'false') . "\n";
            echo "var_dump:\n";
            var_dump($result);
        } catch (\Exception $e) {
            echo "Ошибка: " . $e->getMessage() . "\n";
            var_dump($e);
        }
        echo "\n<hr>\n";

        // 2. getFbsPostings (первые 2 заказа)
        echo "<h2>2. getFbsPostings (за последние 7 дней, первые 2)</h2>";
        $fromDate = Carbon::now()->subDays(7)->toIso8601String();
        $toDate = Carbon::now()->toIso8601String();
        try {
            $fbsOrders = $api->getFbsPostings($fromDate, $toDate);
            $fbsOrders = array_slice($fbsOrders, 0, 2);
            echo "Количество заказов: " . count($fbsOrders) . "\n";
            echo "var_dump:\n";
            var_dump($fbsOrders);
        } catch (\Exception $e) {
            echo "Ошибка: " . $e->getMessage() . "\n";
            var_dump($e);
        }
        echo "\n<hr>\n";

        // 3. getFboPostings
        echo "<h2>3. getFboPostings</h2>";
        try {
            $fboOrders = $api->getFboPostings($fromDate, $toDate);
            $fboOrders = array_slice($fboOrders, 0, 2);
            echo "Количество заказов: " . count($fboOrders) . "\n";
            echo "var_dump:\n";
            var_dump($fboOrders);
        } catch (\Exception $e) {
            echo "Ошибка: " . $e->getMessage() . "\n";
            var_dump($e);
        }
        echo "\n<hr>\n";

        // 4. getProductInfo для первого товара из БД




        echo "<h2>4. getProductInfo (первый товар из последнего заказа в БД)</h2>";
        $order = Order::orderBy('id', 'desc')->first();
        if ($order && $order->items()->count()) {
            $item = $order->items()->first();
            $identifier = $item->offer_id ?? $item->sku; // или sku
            echo "Товар: product_id = {$identifier}, название = {$item->product_name}\n";
            try {
                $productInfo = $api->getProductInfo($identifier);
                echo "var_dump:\n";
                var_dump($productInfo);
            } catch (\Exception $e) {
                echo "Ошибка: " . $e->getMessage() . "\n";
                var_dump($e);
            }
        } else {
            echo "Нет заказов в БД или нет товаров.\n";
        }
        echo "\n<hr>\n";

//        // 5. getChatIdByPostingNumber для того же заказа
//        echo "<h2>5. getChatIdByPostingNumber</h2>";
//        if ($order) {
//            $postingNumber = $order->posting_number;
//            echo "posting_number: {$postingNumber}\n";
//            try {
//                $chatId = $api->getChatIdByPostingNumber($postingNumber);
//                echo "var_dump:\n";
//                var_dump($chatId);
//            } catch (\Exception $e) {
//                echo "Ошибка: " . $e->getMessage() . "\n";
//                var_dump($e);
//            }
//        } else {
//            echo "Нет заказов в БД.\n";
//        }
//        echo "\n<hr>\n";
//
//        // 6. Дерево категорий (v1/description-category/tree) – сырой ответ
//        echo "<h2>6. Дерево категорий (v1/description-category/tree) – первые 10 категорий</h2>";
//        try {
//            $url = "https://api-seller.ozon.ru/v1/description-category/tree";
//            $response = Http::withHeaders([
//                'Client-Id' => config('ozon.client_id'),
//                'Api-Key' => config('ozon.api_key'),
//                'Content-Type' => 'application/json',
//            ])->post($url, ['limit' => 10, 'language' => 'RU']);
//            if ($response->successful()) {
//                $data = $response->json();
//                echo "var_dump:\n";
//                var_dump($data);
//            } else {
//                echo "HTTP ошибка: " . $response->status() . "\n";
//                var_dump($response->body());
//            }
//        } catch (\Exception $e) {
//            echo "Ошибка: " . $e->getMessage() . "\n";
//            var_dump($e);
//        }

        echo "</pre>";
    }
}
