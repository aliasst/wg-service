<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ApiAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PurgeOldOrders extends Command
{
    protected $signature = 'ozon:purge-old-orders
                            {--api_account_id= : ID API-аккаунта (если не указан, очищает все аккаунты)}
                            {--days=14 : Возраст заказов в днях}
                            {--force : Выполнить без подтверждения}';
    protected $description = 'Удаляет заказы старше указанного количества дней вместе с товарами (order_items)';

    public function handle()
    {
        $days = (int) $this->option('days');
        $apiAccountId = $this->option('api_account_id');
        $cutoffDate = Carbon::now()->subDays($days);

        $query = Order::where('created_at', '<', $cutoffDate);

        if ($apiAccountId) {
            $query->where('api_account_id', $apiAccountId);
            $account = ApiAccount::find($apiAccountId);
            $accountName = $account ? $account->name : $apiAccountId;
            $this->info("Очистка заказов для аккаунта: {$accountName}");
        } else {
            $this->info("Очистка заказов для всех аккаунтов");
        }

        $count = $query->count();
        if ($count == 0) {
            $this->info("Нет заказов старше {$days} дней.");
            return 0;
        }

        $this->info("Найдено заказов для удаления: {$count}");

        if (!$this->option('force') && !$this->confirm("Удалить {$count} заказов без возможности восстановления?")) {
            $this->info("Операция отменена.");
            return 0;
        }

        $this->info("Начинаю удаление...");

        // Удаляем связанные товары (order_items) автоматически благодаря foreign key ON DELETE CASCADE
        // Но для безопасности можно удалить их вручную, если каскад не настроен
        $deleted = $query->delete();

        $this->info("Удалено заказов: {$deleted}");

        // Дополнительная очистка order_items, если не были удалены каскадно
        $orphaned = OrderItem::whereDoesntHave('order')->delete();
        if ($orphaned) {
            $this->info("Дополнительно удалено сиротских товаров (без заказов): {$orphaned}");
        }

        return 0;
    }
}
