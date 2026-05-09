<?php

namespace App\Console\Commands;

use App\Models\OrderItem;
use App\Models\ApiAccount;
use App\Services\OzonApiService;
use Illuminate\Console\Command;

class UpdateProductCategories extends Command
{
    protected $signature = 'ozon:update-categories
                            {--api_account_id= : ID API-аккаунта (если не указан, берётся первый активный)}
                            {--limit=100 : Максимальное количество товаров за раз}
                            {--delay=500000 : Задержка между запросами в микросекундах (0.5 сек)}';
    protected $description = 'Обновление категорий товаров (description_category_id) для order_items, где category_id пустой';

    public function handle()
    {
        // ---------- Определяем API-аккаунт ----------
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

        // ---------- Инициализация сервиса ----------
        try {
            $api = new OzonApiService($apiAccount->client_id, $apiAccount->api_key);
        } catch (\Exception $e) {
            $this->error('Ошибка создания OzonApiService: ' . $e->getMessage());
            return 1;
        }

        $limit = (int) $this->option('limit');
        $delay = (int) $this->option('delay');

        $this->info("Обновление категорий для товаров аккаунта: {$apiAccount->name}");
        $this->info("Лимит товаров: {$limit}, задержка: {$delay} мкс");

        // ---------- Выбираем товары без категорий ----------
        // Для FBS товаров у нас есть `offer_id`, для FBO тоже. Используем `offer_id`, если есть, иначе sku.
        $items = OrderItem::whereNull('category_id')
            ->where('sku', '!=', '')
            ->limit($limit)
            ->get();

        if ($items->isEmpty()) {
            $this->info('Нет товаров без категорий.');
            return 0;
        }

        $this->info("Найдено товаров для обновления: " . $items->count());

        $updated = 0;
        $errors = 0;

        foreach ($items as $item) {
            // Определяем идентификатор: приоритет offer_id, затем sku (product_id)
            $identifier = $item->offer_id ?? $item->sku;
            if (empty($identifier)) {
                $this->warn("Товар ID {$item->id}: нет offer_id и sku – пропускаем.");
                $errors++;
                continue;
            }

            try {
                $productInfo = $api->getProductInfo($identifier);
                $categoryId = $productInfo['description_category_id'] ?? null;

                if ($categoryId) {
                    $item->category_id = $categoryId;
                    $item->save();
                    $this->line("✅ Товар {$item->id} (sku: {$item->sku}) – категория: {$categoryId}");
                    $updated++;
                } else {
                    $this->warn("⚠️ Товар {$item->id} – категория не найдена (description_category_id отсутствует)");
                    $errors++;
                }
            } catch (\Exception $e) {
                $this->error("❌ Ошибка для товара {$item->id} ({$identifier}): " . $e->getMessage());
                $errors++;
            }

            // Задержка, чтобы не превысить лимиты API Ozon
            if ($delay > 0) {
                usleep($delay);
            }
        }

        $this->info("Обновление завершено. Обновлено: {$updated}, ошибок: {$errors}");
        return 0;
    }
}
