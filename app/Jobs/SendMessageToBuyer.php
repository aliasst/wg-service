<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\MessageLog;
use App\Models\ApiAccount;
use App\Services\OzonApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;

class SendMessageToBuyer implements ShouldQueue
{
    use Queueable, InteractsWithQueue;

    protected $order;
    protected $header;
    protected $body;
    protected $messageId;

    public function __construct(Order $order, $header, $body, $messageId)
    {
        $this->order = $order;
        $this->header = $header;
        $this->body = $body;
        $this->messageId = $messageId;
    }

    public function handle()
    {
        // Получаем API-аккаунт, к которому привязан заказ
        $apiAccount = ApiAccount::find($this->order->api_account_id);
        if (!$apiAccount || !$apiAccount->is_active) {
            $this->logError('API-аккаунт не активен или не найден.');
            return;
        }

        $api = new OzonApiService($apiAccount->client_id, $apiAccount->api_key);
        $chatId = $this->order->chat_id;
        $postingNumber = $this->order->posting_number;

        // Логика для FBS: можем создать чат, если его нет
        if ($this->order->order_type === 'fbs') {
            if (!$chatId) {
                \Log::info('Пробуем найти существующий чат');
                // Пробуем найти существующий чат
                $chatId = $api->getChatIdByPostingNumber($postingNumber);
                if (!$chatId) {
                    \Log::info('Создаём новый чат');
                    // Создаём новый чат
                    $chatId = $api->startChat($postingNumber);
                }
                if ($chatId) {
                    $this->order->update(['chat_id' => $chatId]);
                }
            }
        } else { // FBO – только если чат уже существует
            if (!$chatId) {
                $this->logError('FBO заказ: чат не открыт, отправка невозможна.');
                return;
            }
        }

        if (!$chatId) {
            $this->logError('Не удалось получить или создать чат для отправления ' . $postingNumber);
            return;
        }

        $fullText = $this->header . "\n\n" . $this->body;
        try {
            $api->sendMessage($chatId, $fullText);
            $this->logSuccess();
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
        }
    }

    private function logSuccess()
    {
        MessageLog::create([
            'message_id' => $this->messageId,
            'order_id' => $this->order->id,
            'recipient_name' => $this->order->customer_name,
            'sent_at' => now(),
            'status' => 'success',
        ]);
    }

    private function logError($error)
    {
        MessageLog::create([
            'message_id' => $this->messageId,
            'order_id' => $this->order->id,
            'recipient_name' => $this->order->customer_name,
            'sent_at' => now(),
            'status' => 'error',
            'error_text' => $error,
        ]);
    }
}
