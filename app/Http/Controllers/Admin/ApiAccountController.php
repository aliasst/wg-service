<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ApiAccountController extends Controller
{
    public function index()
    {
        $accounts = ApiAccount::orderBy('id')->get();
        return view('admin.api_accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('admin.api_accounts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'client_id' => 'required|string|max:255|unique:api_accounts,client_id',
            'api_key' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        ApiAccount::create([
            'name' => $validated['name'],
            'client_id' => $validated['client_id'],
            'api_key' => $validated['api_key'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.api_accounts.index')
            ->with('success', 'API-аккаунт добавлен.');
    }

    public function edit(ApiAccount $account)
    {
        return view('admin.api_accounts.edit', compact('account'));
    }

    public function update(Request $request, ApiAccount $account)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'client_id' => 'required|string|max:255|unique:api_accounts,client_id,' . $account->id,
            'api_key' => 'nullable|string', // необязательно при обновлении
            'is_active' => 'nullable|boolean',
        ]);

        $data = [
            'name' => $validated['name'],
            'client_id' => $validated['client_id'],
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('api_key')) {
            $data['api_key'] = $validated['api_key'];
        }

        $account->update($data);

        return redirect()->route('admin.api_accounts.index')
            ->with('success', 'API-аккаунт обновлён.');
    }

    public function destroy(ApiAccount $account)
    {
        // Запрещаем удалять единственный активный аккаунт (можно опционально)
        if (ApiAccount::where('is_active', true)->count() === 1 && $account->is_active) {
            return redirect()->route('admin.api_accounts.index')
                ->with('error', 'Нельзя удалить единственный активный API-аккаунт.');
        }
        $account->delete();
        return redirect()->route('admin.api_accounts.index')
            ->with('success', 'API-аккаунт удалён.');
    }
}
