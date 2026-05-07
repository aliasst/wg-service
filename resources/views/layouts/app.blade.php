<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ozon Bot')</title>
    <link rel="stylesheet" href="{{ asset('assets/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.min.css') }}">
    @stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-md navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('admin.dashboard') }}">Ozon Bot</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Главная</a></li>
                @can('admin')
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.api_accounts.index') }}">API-аккаунты</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.users.index') }}">Пользователи</a></li>
                @endcan
                <!-- Временные заглушки – позже заменим на реальные маршруты -->
                @can('admin')
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.categories.index') }}">Категории</a></li>
                @endcan
                @can('admin')
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.products.index') }}">Товары</a></li>
                @endcan
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.messages.buyers') }}">Рассылка</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.messages.history') }}">История</a></li>
                @php
                    $currentAccount = app('currentApiAccount');
                    $accounts = App\Models\ApiAccount::where('is_active', true)->get();
                @endphp

                @if($accounts->count() > 1)
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            {{ $currentAccount->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @foreach($accounts as $account)
                                <li>
                                    <form method="POST" action="{{ route('admin.switch.account') }}" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="api_account_id" value="{{ $account->id }}">
                                        <button type="submit" class="dropdown-item {{ $currentAccount->id == $account->id ? 'active' : '' }}">
                                            {{ $account->name }}
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @elseif($accounts->count() == 1)
                    <li class="nav-item"><span class="nav-link">{{ $currentAccount->name }}</span></li>
                @endif

                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button class="btn btn-outline-light ms-2" type="submit">Выйти</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container mt-4">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @yield('content')
</main>

<script src="{{ asset('assets/bootstrap.bundle.min.js') }}"></script>
@stack('scripts')
</body>
</html>
