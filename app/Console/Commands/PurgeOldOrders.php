<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MessageLog;
use App\Models\ApiAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PurgeOldOrders extends Command
{
    protected $signature = 'ozon:purge-old-orders
                            {--api_account_id= : ID API-аккаунта (если не указан, очищает все аккаунты)}
                            {--days=14 : Возраст заказов в днях}
                            {--force : Выполнить без подтверждения}';
    protected $description = 'Удаляет заказы старше указанного количества дней вместе с товарами и логами';

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

        $orders = $query->get();
        $count = $orders->count();
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

        $deletedLogs = 0;
        $deletedOrderItems = 0;
        $deletedOrders = 0;

        foreach ($orders as $order) {
            // Удаляем логи сообщений, связанные с этим заказом
            $logCount = MessageLog::where('order_id', $order->id)->delete();
            $deletedLogs += $logCount;

            // Удаляем товары заказа (если не каскадно)
            $itemCount = OrderItem::where('order_id', $order->id)->delete();
            $deletedOrderItems += $itemCount;

            // Удаляем сам заказ
            $order->delete();
            $deletedOrders++;
        }

        $this->info("Удалено логов отправки: {$deletedLogs}");
        $this->info("Удалено товаров: {$deletedOrderItems}");
        $this->info("Удалено заказов: {$deletedOrders}");

        return 0;
    }
}
