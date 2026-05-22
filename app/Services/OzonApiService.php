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
     * Получение заказов FBS через API v4 с поддержкой пагинации (курсор)
     *
     * @param string $fromDate
     * @param string $toDate
     * @param int $limit  Количество заказов на страницу (макс. 100)
     * @param string $cursor  Курсор для следующей страницы (пустая строка для первой)
     * @return array ['items' => [], 'has_next' => bool, 'cursor' => string]
     */
    public function getFbsPostingsPaginated(string $fromDate, string $toDate, int $limit = 100, string $cursor = ''): array
    {
        $url = $this->baseUrl . '/v4/posting/fbs/list';
        $payload = [
            'sort_dir' => 'asc',
            'filter' => [
                'since' => $fromDate,
                'to'    => $toDate,
                'status' => ['awaiting_packaging', 'awaiting_deliver', 'delivering', 'delivered', 'cancelled'],
            ],
            'limit' => min($limit, 100),
            'cursor' => $cursor,
            'with' => [
                'analytics_data' => false,
                'barcodes'       => true,
                'financial_data' => false,
                'legal_info'     => true,
                'translit'       => false,
            ],
        ];
        $response = Http::withHeaders($this->headers())->post($url, $payload);
        $response->throw();
        $data = $response->json();
        return [
            'items'    => $data['postings'] ?? [],
            'has_next' => $data['has_next'] ?? false,
            'cursor'   => $data['cursor'] ?? '',
        ];
    }


    /**
     * Получение заказов FBS через API v4 (один запрос, без пагинации)
     */
    /**
     * Получение заказов FBS через API v4 (один запрос, без пагинации)
     */
    public function getFbsPostings(string $fromDate, string $toDate, int $limit = 100): array
    {
        $url = $this->baseUrl . '/v4/posting/fbs/list';
        $payload = [
            'sort_dir' => 'asc',
            'filter' => [
                'since' => $fromDate,
                'to'    => $toDate,
                'status' => ['awaiting_packaging', 'awaiting_deliver', 'delivering', 'delivered', 'cancelled'],
            ],
            'limit'  => min($limit, 100),
            'cursor' => '',
            'with'   => [
                'analytics_data' => false,
                'barcodes'       => true,
                'financial_data' => false,
                'legal_info'     => true,
                'translit'       => false,
            ],
        ];
        $response = Http::withHeaders($this->headers())->post($url, $payload);
        $response->throw();
        $data = $response->json();
        // В v4 заказы находятся в корневом ключе 'postings'
        return $data['postings'] ?? [];
    }

    /**
     * Получение заказов FBO с пагинацией (курсор)
     *
     * @param string $fromDate
     * @param string $toDate
     * @param int $limit
     * @param string $cursor
     * @return array ['items' => [], 'has_next' => bool, 'cursor' => string]
     */
    public function getFboPostingsPaginated(string $fromDate, string $toDate, int $limit = 100, string $cursor = ''): array
    {
        $url = $this->baseUrl . '/v3/posting/fbo/list';
        $payload = [
            'filter' => [
                'since' => $fromDate,
                'to'    => $toDate,
            ],
            'limit'  => min($limit, 100),
            'cursor' => $cursor,
            'with'   => [
                'analytics_data' => true,
                'financial_data' => false,
                'legal_info'     => true,
            ],
        ];
        $response = Http::withHeaders($this->headers())->post($url, $payload);
        $response->throw();
        $data = $response->json();
        return [
            'items'    => $data['postings'] ?? [],
            'has_next' => $data['has_next'] ?? false,
            'cursor'   => $data['cursor'] ?? '',
        ];
    }

    /**
     * Получение заказов FBO через API v3 (современный формат запроса, без пагинации)
     */
    public function getFboPostings(string $fromDate, string $toDate, int $limit = 100): array
    {
        $url = $this->baseUrl . '/v3/posting/fbo/list';
        $payload = [
            'dir'      => 'asc',        // или 'sort_dir' – проверьте по документации
            'filter'   => [
                'since' => $fromDate,
                'to'    => $toDate,
            ],
            'limit'    => min($limit, 100),
            'cursor'   => '',
            'with'     => [
                'analytics_data' => false,
                'financial_data' => false,
                'legal_info'     => true,
            ],
        ];
        $response = Http::withHeaders($this->headers())->post($url, $payload);
        $response->throw();
        $data = $response->json();
        // В v3 для FBO заказы лежат в корневом ключе 'postings'
        return $data['postings'] ?? [];
    }

    public function getChatIdByPostingNumber(string $postingNumber): ?string
    {
        $url = $this->baseUrl . '/v1/chat/list';
        $payload = ['filter' => ['posting_number' => $postingNumber], 'limit' => 1];

        \Log::info('getChatIdByPostingNumber request', [
            'posting_number' => $postingNumber,
            'url' => $url,
            'payload' => $payload,
        ]);

        $response = Http::withHeaders($this->headers())->post($url, $payload);

        \Log::info('getChatIdByPostingNumber response', [
            'posting_number' => $postingNumber,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        if ($response->status() === 404) {
            \Log::warning('Chat list endpoint returned 404 (perhaps method deprecated?)', ['posting_number' => $postingNumber]);
            return null;
        }

        if (!$response->successful()) {
            \Log::error('getChatIdByPostingNumber failed', [
                'posting_number' => $postingNumber,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $data = $response->json();
        $chats = $data['chats'] ?? [];
        $chatId = $chats[0]['chat_id'] ?? null;

        \Log::info('getChatIdByPostingNumber result', [
            'posting_number' => $postingNumber,
            'chat_id' => $chatId,
        ]);

        return $chatId;
    }

    /**
     * Создать новый чат для отправления FBS
     */
    public function startChat(string $postingNumber): ?string
    {
        $url = $this->baseUrl . '/v1/chat/start';
        $payload = ['posting_number' => $postingNumber];

        \Log::info('startChat request', [
            'posting_number' => $postingNumber,
            'url' => $url,
            'payload' => $payload,
        ]);

        $response = Http::withHeaders($this->headers())->post($url, $payload);

        \Log::info('startChat response', [
            'posting_number' => $postingNumber,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        if (!$response->successful()) {
            $errorBody = $response->json();
            $errorMsg = $errorBody['message'] ?? 'Неизвестная ошибка API';
            \Log::error('startChat failed', [
                'posting_number' => $postingNumber,
                'status' => $response->status(),
                'error' => $errorMsg,
                'full_body' => $response->body(),
            ]);
            throw new \Exception($errorMsg);
        }

        $data = $response->json();
        $chatId = $data['result']['chat_id'] ?? null;   // ← ключ 'result', а не корень

        \Log::info('startChat success', [
            'posting_number' => $postingNumber,
            'chat_id' => $chatId,
        ]);

        return $chatId;
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

        \Log::info('sendMessage request', [
            'chat_id' => $chatId,
            'url' => $url,
            'text_preview' => substr($text, 0, 200),
        ]);

        $response = Http::withHeaders($this->headers())->post($url, $payload);

        \Log::info('sendMessage response', [
            'chat_id' => $chatId,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        if (!$response->successful()) {
            \Log::error('sendMessage failed', [
                'chat_id' => $chatId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            $response->throw();
        }

        return $response->json();
    }



}
