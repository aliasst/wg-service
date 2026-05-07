<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class OzonApiService
{
    protected string $clientId;
    protected string $apiKey;
    protected string $baseUrl = 'https://api-seller.ozon.ru';

    public function __construct(string $clientId, string $apiKey)
    {
        $this->clientId = $clientId;
        $this->apiKey = $apiKey;
    }

    protected function headers(): array
    {
        return [
            'Client-Id' => $this->clientId,
            'Api-Key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Проверка соединения с API Ozon
     */
    public function checkConnection(): bool
    {
        try {
            $url = $this->baseUrl . '/v1/description-category/tree';
            $response = Http::withHeaders($this->headers())
                ->post($url, [
                    'limit' => 1,
                    'language' => 'RU'
                ]);

            // Логируем полный URL и ответ, который вернул сервер
//            \Log::channel('stack')->info('API Request to: ' . $url);
//            \Log::channel('stack')->info('API Response: ' . $response->body());

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Получение заказов FBS (доставленные)
     */
    public function getFbsPostings(string $fromDate, string $toDate): array
    {
        $url = $this->baseUrl . '/v3/posting/fbs/list';
        $payload = [
            'filter' => [
                'since' => $fromDate,
                'to' => $toDate,
                'status' => 'delivered'
            ],
            'limit' => 100
        ];
        $response = Http::withHeaders($this->headers())->post($url, $payload);
        $response->throw();
        return $response->json()['result']['postings'] ?? [];
    }

    /**
     * Получение заказов FBO (доставленные)
     */
    public function getFboPostings(string $fromDate, string $toDate): array
    {
        $url = $this->baseUrl . '/v3/posting/fbo/list';
        $payload = [
            'filter' => [
                'since' => $fromDate,
                'to' => $toDate,
                'status' => 'delivered'
            ],
            'limit' => 100
        ];
        $response = Http::withHeaders($this->headers())->post($url, $payload);
        $response->throw();
        return $response->json()['result']['postings'] ?? [];
    }

    /**
     * Получить chat_id по номеру отправления
     */
    public function getChatIdByPostingNumber(string $postingNumber): ?string
    {
        $url = $this->baseUrl . '/v1/chat/list';
        $payload = [
            'filter' => ['posting_number' => $postingNumber],
            'limit' => 1
        ];
        $response = Http::withHeaders($this->headers())->post($url, $payload);
        $data = $response->json();
        $chats = $data['chats'] ?? [];
        return $chats[0]['chat_id'] ?? null;
    }

    /**
     * Создать новый чат для отправления FBS
     */
    public function startChat(string $postingNumber): ?string
    {
        try {
            $url = $this->baseUrl . '/v1/chat/start';
            $payload = ['posting_number' => $postingNumber];
            $response = Http::withHeaders($this->headers())->post($url, $payload);
            $data = $response->json();
            return $data['chat_id'] ?? null;
        } catch (\Exception $e) {
            \Log::error('Ошибка при создании чата: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Получение информации о товаре (категория и др.)
     */
    public function getProductInfo($offer_id): array
    {

        $url = $this->baseUrl . '/v3/product/info/list';

        // Если identifier похож на число – ищем по product_id, иначе по offer_id

        $payload = ['offer_id' => [(string)$offer_id]];


        $response = Http::withHeaders($this->headers())->post($url, $payload);
        $response->throw();
        $data = $response->json();
        $items = $data['items'] ?? [];
        return $items[0] ?? [];
    }

    public function sendMessage(string $chatId, string $text): array
    {
        $url = $this->baseUrl . '/v1/chat/messages';
        $payload = [
            'chat_id' => $chatId,
            'message' => ['text' => $text],
        ];
        $response = Http::withHeaders($this->headers())->post($url, $payload);
        $response->throw();
        return $response->json();
    }



}
