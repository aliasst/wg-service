<?php

namespace App\Http\Middleware;

use App\Models\ApiAccount;
use Closure;
use Illuminate\Http\Request;

class SetApiAccount
{
    public function handle(Request $request, Closure $next)
    {
        // Если есть аккаунт в сессии – используем его
        if (session()->has('api_account_id')) {
            $account = ApiAccount::find(session('api_account_id'));
            if ($account && $account->is_active) {
                app()->instance('currentApiAccount', $account);
                return $next($request);
            }
        }

        // Иначе берём первый активный аккаунт
        $account = ApiAccount::where('is_active', true)->first();
        if ($account) {
            session(['api_account_id' => $account->id]);
            app()->instance('currentApiAccount', $account);
        }

        return $next($request);
    }
}
