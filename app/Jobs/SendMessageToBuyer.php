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
        \Log::info('SendMessageToBuyer started', [
            'order_id' => $this->order->id,
            'posting_number' => $this->order->posting_number,
            'order_type' => $this->order->order_type,
            'has_chat_id' => !empty($this->order->chat_id),
            'message_id' => $this->messageId,
        ]);


        $apiAccount = ApiAccount::find($this->order->api_account_id);
        if (!$apiAccount || !$apiAccount->is_active) {
            $this->logError('API-аккаунт не активен или не найден.');
            return;
        }

        $api = new OzonApiService($apiAccount->client_id, $apiAccount->api_key);
        $chatId = $this->order->chat_id;
        $postingNumber = $this->order->posting_number;
        $recipientName = $this->order->customer_name ?? 'Неизвестный';

        // FBS: пытаемся получить или создать чат
        if ($this->order->order_type === 'fbs') {
            if (!$chatId) {
                \Log::info('Пробуем найти существующий чат');
                $chatId = $api->getChatIdByPostingNumber($postingNumber);
                if (!$chatId) {
                    \Log::info('Создаём новый чат');
                    try {
                        $chatId = $api->startChat($postingNumber);
                        if ($chatId) {
                            $this->order->update(['chat_id' => $chatId]);
                        }
                    } catch (\Exception $e) {
                        // Преобразуем сообщение об ошибке в понятное для администратора
                        $errorMsg = $e->getMessage();
                        if (str_contains($errorMsg, 'access period has expired')) {
                            $errorMsg = 'Истёк срок для отправки сообщения (с момента заказа прошло более 3 дней). Чат не может быть открыт.';
                        }
                        $this->logError($errorMsg, $recipientName);
                        return;
                    }
                }
            }
        } else { // FBO
            if (!$chatId) {
                $this->logError('FBO заказ: чат не открыт, отправка невозможна.', $recipientName);
                return;
            }
        }

        if (!$chatId) {
            $this->logError('Не удалось получить или создать чат для отправления ' . $postingNumber, $recipientName);
            return;
        }

        $fullText = $this->header . "\n\n" . $this->body;
        try {
            $api->sendMessage($chatId, $fullText);
            $this->logSuccess($recipientName);
        } catch (\Exception $e) {
            $this->logError($e->getMessage(), $recipientName);
        }
    }

    private function logSuccess($recipientName)
    {
        MessageLog::create([
            'message_id'      => $this->messageId,
            'order_id'        => $this->order->id,
            'recipient_name'  => $recipientName,
            'sent_at'         => now(),
            'status'          => 'success',
        ]);
    }

    private function logError($error, $recipientName = null)
    {
        if ($recipientName === null) {
            $recipientName = $this->order->customer_name ?? 'Неизвестный';
        }
        MessageLog::create([
            'message_id'      => $this->messageId,
            'order_id'        => $this->order->id,
            'recipient_name'  => $recipientName,
            'sent_at'         => now(),
            'status'          => 'error',
            'error_text'      => $error,
        ]);
    }
}
