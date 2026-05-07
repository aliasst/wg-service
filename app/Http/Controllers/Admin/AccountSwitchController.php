<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountSwitchController extends Controller
{
    public function switch(Request $request)
    {
        $request->validate(['api_account_id' => 'required|exists:api_accounts,id']);
        session(['api_account_id' => $request->api_account_id]);
        return redirect()->back()->with('success', 'Аккаунт переключён');
    }
}
