<?php

use App\Http\Controllers\Admin\ApiAccountController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProductsController;
use App\Http\Controllers\Admin\CategoryMappingController;
use App\Http\Controllers\Admin\TestController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AccountSwitchController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/wg-sms-ozon123', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/wg-sms-ozon123', [LoginController::class, 'login']);
});

Route::post('/logout', [LogoutController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'set.api.account'])->prefix('admin')->name('admin.')->group(function () {
    // Общие маршруты для всех авторизованных
    Route::get('/test', [TestController::class, 'index'])->name('test');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/check-ozon', [DashboardController::class, 'checkOzon'])->name('check.ozon');
    Route::post('/sync-orders', [OrderController::class, 'sync'])->name('orders.sync');
    Route::post('/switch-account', [AccountSwitchController::class, 'switch'])->name('switch.account');

    // Рассылка
    Route::get('/messages/buyers', [MessageController::class, 'showBuyers'])->name('messages.buyers');
    Route::post('/messages/send', [MessageController::class, 'send'])->name('messages.send');
    Route::get('/messages/history', [MessageController::class, 'history'])->name('messages.history');
    // (опционально) Route::get('/messages/{message}/logs', [MessageController::class, 'logs'])->name('messages.logs');

    // Маршруты только для администраторов
    Route::middleware('can:admin')->group(function () {
        Route::get('/products', [ProductsController::class, 'index'])->name('products.index');

        // API-аккаунты
        Route::get('/api-accounts', [ApiAccountController::class, 'index'])->name('api_accounts.index');
        Route::get('/api-accounts/create', [ApiAccountController::class, 'create'])->name('api_accounts.create');
        Route::post('/api-accounts', [ApiAccountController::class, 'store'])->name('api_accounts.store');
        Route::get('/api-accounts/{account}/edit', [ApiAccountController::class, 'edit'])->name('api_accounts.edit');
        Route::put('/api-accounts/{account}', [ApiAccountController::class, 'update'])->name('api_accounts.update');
        Route::delete('/api-accounts/{account}', [ApiAccountController::class, 'destroy'])->name('api_accounts.destroy');

        // Пользователи
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Категории
        Route::get('/categories', [CategoryMappingController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryMappingController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryMappingController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryMappingController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryMappingController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryMappingController::class, 'destroy'])->name('categories.destroy');
    });
});
