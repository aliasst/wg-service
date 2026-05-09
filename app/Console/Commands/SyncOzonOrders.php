<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ApiAccount;
use App\Services\OzonApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SyncOzonOrders extends Command
{
    protected $signature = 'ozon:sync {--days=30} {--user_id=1} {--api_account_id=} {--limit=100}';
    protected $description = 'Синхронизация заказов Ozon: FBS (v4), FBO (v3) – один запрос без пагинации';

    public function handle()
    {
        set_time_limit(0);
        // ---------- 1. API-аккаунт ----------
        $apiAccountId = $this->option('api_account_id');
        if (!$apiAccountId) {
            $apiAccountId = session('api_account_id');
        }
        if (!$apiAccountId) {
            $first = ApiAccount::where('is_active', true)->first();
            if ($first) {
                $apiAccountId = $first->id;
                $this->info("Используем первый активный аккаунт: {$first->name}");
            } else {
                $this->error('Нет активного API-аккаунта.');
                return 1;
            }
        }

        $apiAccount = ApiAccount::find($apiAccountId);
        if (!$apiAccount || !$apiAccount->is_active) {
            $this->error('API-аккаунт не найден или неактивен.');
            return 1;
        }

        // ---------- 2. Сервис ----------
        try {
            $api = new OzonApiService($apiAccount->client_id, $apiAccount->api_key);
        } catch (\Exception $e) {
            $this->error('Ошибка создания OzonApiService: ' . $e->getMessage());
            return 1;
        }

        $userId = (int) $this->option('user_id');
        $days   = (int) $this->option('days');
        $limit  = (int) $this->option('limit');
        //$limit  = min($limit, 100);
        $limit  = 100; //временно


        $fromDate = Carbon::now()->subDays($days)->toIso8601String();
        $toDate   = Carbon::now()->toIso8601String();

        $this->info("Синхронизация для аккаунта: {$apiAccount->name} ({$apiAccount->client_id})");
        $this->info("Период: {$days} дней (с {$fromDate} по {$toDate})");
        $this->info("Лимит (макс. 100): {$limit}");

        // ---------- 3. Получаем заказы ----------
        try {
            $cursor = '';
            $allFbsRaw = [];
            $page = 1;
            do {
                $result = $api->getFbsPostingsPaginated($fromDate, $toDate, $limit, $cursor);
                $allFbsRaw = array_merge($allFbsRaw, $result['items']);
                $this->info("FBS страница {$page}: загружено " . count($result['items']) . " заказов, has_next = " . ($result['has_next'] ? 'true' : 'false'));
                $cursor = $result['cursor'];
                $page++;
            } while ($result['has_next']);

                $fbsOrdersRaw = $allFbsRaw;


            $fboOrdersRaw = $api->getFboPostings($fromDate, $toDate, $limit);
//        $fboOrdersRaw = []; // временно
        } catch (\Exception $e) {
            $this->error('Ошибка получения заказов: ' . $e->getMessage());
            return 1;
        }

        // Приводим FBS-заказы к единому формату
        $fbsOrders = array_map(function ($order) {
            // Дата доставки: фактическая delivery_date (если null, то доставка ещё не произошла)
            $deliveryDateStr = $order['delivery_date'] ?? null;
            // Дата оплаты/обработки
            $paymentDateStr = $order['in_process_at'] ?? null;

            return [
                'posting_number'   => $order['posting_number'],
                'order_id'         => $order['order_id'],
                'customer_name'    => $order['customer']['name'] ?? null,
                'customer_phone'   => $order['customer']['phone'] ?? null,
                'status'           => $order['status_alias'] ?? $order['status'] ?? null,
                'delivery_date'    => $deliveryDateStr,
                'payment_date'     => $paymentDateStr,
                'products'         => array_map(function ($p) {
                    return [
                        'sku'          => (string)($p['sku'] ?? ''),
                        'offer_id'     => $p['offer_id'] ?? null,
                        'name'         => $p['name'],
                        'quantity'     => $p['quantity'],
                        'price'        => $p['price'],
                    ];
                }, $order['products'] ?? []),
                'order_type'       => 'fbs',
            ];
        }, $fbsOrdersRaw);

        // Приводим FBO-заказы к единому формату
        $fboOrders = array_map(function ($order) {
            // Для FBO берём плановую дату доставки из analytics_data
            $deliveryDateStr = $order['analytics_data']['client_delivery_date_begin'] ?? null;
            $paymentDateStr = $order['in_process_at'] ?? null;

            return [
                'posting_number'   => $order['posting_number'],
                'order_id'         => $order['order_id'],
                'customer_name'    => null,
                'customer_phone'   => null,
                'status'           => $order['status'] ?? null,
                'delivery_date'    => $deliveryDateStr,
                'payment_date'     => $paymentDateStr,
                'products'         => array_map(function ($p) {
                    return [
                        'sku'          => (string)($p['sku'] ?? ''),
                        'offer_id'     => $p['offer_id'] ?? null,
                        'name'         => $p['name'],
                        'quantity'     => $p['quantity'],
                        'price'        => $p['price'],
                    ];
                }, $order['products'] ?? []),
                'order_type'       => 'fbo',
            ];
        }, $fboOrdersRaw);

        $allOrders = array_merge($fbsOrders, $fboOrders);

        $this->info("Получено FBS: " . count($fbsOrders) . ", FBO: " . count($fboOrders));
        $this->info("Всего к обработке: " . count($allOrders));

        if (empty($allOrders)) {
            $this->info("Нет заказов за указанный период.");
            return 0;
        }

        // ---------- 4. Обработка каждого заказа ----------
        foreach ($allOrders as $posting) {
            // Проверка чата
            $chatId = null;
            try {
                $chatId = $api->getChatIdByPostingNumber($posting['posting_number']);
                if ($chatId) {
                    $this->line("Найден чат для {$posting['posting_number']}: {$chatId}");
                }
            } catch (\Exception $e) {
                $this->warn("Ошибка проверки чата: " . $e->getMessage());
            }

            // Конвертируем строки дат в Carbon (или null)
            $deliveryDate = $posting['delivery_date'] ? Carbon::parse($posting['delivery_date']) : null;
            $paymentDate  = $posting['payment_date']  ? Carbon::parse($posting['payment_date'])  : null;

            // Сохраняем заказ
            $order = Order::updateOrCreate(
                ['posting_number' => $posting['posting_number']],
                [
                    'order_id'       => $posting['order_id'],
                    'customer_name'  => $posting['customer_name'],
                    'customer_phone' => $posting['customer_phone'],
                    'status'         => $posting['status'],
                    'delivery_date'  => $deliveryDate,
                    'payment_date'   => $paymentDate,
                    'user_id'        => $userId,
                    'order_type'     => $posting['order_type'],
                    'chat_id'        => $chatId,
                    'api_account_id' => $apiAccount->id,
                ]
            );

            // Сохраняем товары
            foreach ($posting['products'] as $product) {
                $categoryId = null;
//                try {
//                    $identifier = $product['offer_id'] ?? $product['sku'];
//                    $productInfo = $api->getProductInfo($identifier);
//                    $categoryId = $productInfo['description_category_id'] ?? null;
//                } catch (\Exception $e) {
//                    $this->warn("Не удалось получить категорию для товара: {$product['name']}");
//                }



                // Извлекаем количество
                $quantity = is_array($product['quantity']) ? 0 : (int)$product['quantity'];

                // Извлекаем цену
                if (is_array($product['price'])) {
                    $price = ($product['price']['units'] ?? 0) + ($product['price']['nanos'] ?? 0) / 1e9;
                } else {
                    $price = (float)$product['price'];
                }

                OrderItem::updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'sku'      => (string)$product['sku'],
                    ],
                    [
                        'offer_id'     => $product['offer_id'] ?? null,
                        'product_name' => $product['name'],
                        'quantity'     => $quantity,
                        'price'        => $price,
//                        'category_id'  => $categoryId,
                    ]
                );
            }
        }

        $this->info("Синхронизация завершена. Обработано заказов: " . count($allOrders));
        return 0;
    }
}
