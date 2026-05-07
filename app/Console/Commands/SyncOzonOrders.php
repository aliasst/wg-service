<?php

namespace App\Console\Commands;

use App\Models\ApiAccount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OzonApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SyncOzonOrders extends Command
{
    protected $signature = 'ozon:sync {--days=30} {--user_id=1} {--api_account_id=}';
    protected $description = 'Синхронизация заказов Ozon (FBS и FBO) с проверкой существующего чата';

    public function handle()
    {
        // 1. Определяем api_account_id
        $apiAccountId = $this->option('api_account_id');
        if (!$apiAccountId) {
            $apiAccountId = session('api_account_id');
        }
        if (!$apiAccountId) {
            $first = ApiAccount::where('is_active', true)->first();
            if ($first) {
                $apiAccountId = $first->id;
                $this->info("Используем первый активный API-аккаунт: {$first->name}");
            } else {
                $this->error('Нет активного API-аккаунта. Сначала создайте аккаунт через админку или сидер.');
                return 1;
            }
        }

        $apiAccount = ApiAccount::find($apiAccountId);
        if (!$apiAccount || !$apiAccount->is_active) {
            $this->error('API-аккаунт не найден или неактивен.');
            return 1;
        }

        // 2. Создаём сервис с передачей параметров
        try {
            $api = new OzonApiService($apiAccount->client_id, $apiAccount->api_key);
        } catch (\Exception $e) {
            $this->error('Ошибка создания OzonApiService: ' . $e->getMessage());
            return 1;
        }

        $userId = $this->option('user_id');
        $days = (int) $this->option('days');
        $fromDate = Carbon::now()->subDays($days)->toIso8601String();
        $toDate = Carbon::now()->toIso8601String();


        $this->info("Синхронизация заказов за {$days} дней (с {$fromDate} по {$toDate})");

        // Получаем заказы (предполагается, что методы возвращают массив заказов)
        $fbsOrders = $api->getFbsPostings($fromDate, $toDate);
        $fboOrders = $api->getFboPostings($fromDate, $toDate);

        // Добавляем тип заказа
        $fbsOrders = array_map(fn($order) => $order + ['order_type' => 'fbs'], $fbsOrders);
        $fboOrders = array_map(fn($order) => $order + ['order_type' => 'fbo'], $fboOrders);

        $allOrders = array_merge($fbsOrders, $fboOrders);

        $this->info("Получено заказов: FBS=" . count($fbsOrders) . ", FBO=" . count($fboOrders));



//        $counter = 50;
        foreach ($allOrders as $posting) {
            // Проверяем, есть ли уже открытый чат (не создаём новый)
            $chatId = null;
            try {
                $chatId = $api->getChatIdByPostingNumber($posting['posting_number']);
                if ($chatId) {
                    $this->line("Для заказа {$posting['posting_number']} найден chat_id: {$chatId}");
                }
            } catch (\Exception $e) {
                $this->warn("Ошибка проверки чата для {$posting['posting_number']}: " . $e->getMessage());
            }

            // Сохраняем заказ
            $order = Order::updateOrCreate(
                ['posting_number' => $posting['posting_number']],
                [
                    'order_id' => $posting['order_id'] ?? null,
                    'customer_name' => $posting['customer']['name'] ?? null,
                    'customer_phone' => $posting['customer']['phone'] ?? null,
                    'status' => $posting['status'],
                    'delivery_date' => isset($posting['delivery_date']) ? Carbon::parse($posting['delivery_date']) : null,
                    'user_id' => $userId,
                    'order_type' => $posting['order_type'],
                    'chat_id' => $chatId, // сохраняем, только если есть
                    'api_account_id' => $apiAccount->id,
                ]
            );

            // Сохраняем товары
            foreach ($posting['products'] as $product) {
                $categoryId = null;
                try {
                    // Пытаемся получить категорию, используя offer_id
                    $productInfo = $api->getProductInfo($product['offer_id']);
                    $categoryId = $productInfo['description_category_id'] ?? null;
                } catch (\Exception $e) {
                    $this->warn("Не удалось получить категорию для товара: " . ($product['offer_id'] ?? $product['sku']));
                }

                OrderItem::updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'sku' => (string)$product['sku'],
                    ],
                    [
                        'offer_id' => $product['offer_id'] ?? null,
                        'product_name' => $product['name'],
                        'quantity' => $product['quantity'],
                        'price' => $product['price'],
                        'category_id' => $categoryId,
                    ]
                );
            }
            // временное ограничение
//            $counter++;
//            if ($counter >= 3) break;
        }

        $this->info("Синхронизация завершена. Обработано заказов: " . count($allOrders));
    }
}
