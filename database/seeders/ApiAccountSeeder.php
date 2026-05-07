<?php

namespace Database\Seeders;

use App\Models\ApiAccount;
use Illuminate\Database\Seeder;

class ApiAccountSeeder extends Seeder
{
    public function run()
    {
        // Проверяем, есть ли уже аккаунт с таким client_id из .env
        $clientId = env('OZON_CLIENT_ID');
        if (!$clientId) {
            $this->command->warn('OZON_CLIENT_ID не задан в .env. API-аккаунт не создан.');
            return;
        }

        $exists = ApiAccount::where('client_id', $clientId)->exists();
        if ($exists) {
            $this->command->info('API-аккаунт с таким client_id уже существует.');
            return;
        }

        $apiKey = env('OZON_API_KEY');
        $name = env('API_ACCOUNT_NAME', 'Основной магазин');

        if (!$apiKey) {
            $this->command->warn('OZON_API_KEY не задан в .env. API-аккаунт не создан.');
            return;
        }

        ApiAccount::create([
            'name' => $name,
            'client_id' => $clientId,
            'api_key' => $apiKey,
            'is_active' => true,
        ]);

        $this->command->info('API-аккаунт "'.$name.'" создан.');
    }
}
