@extends('layouts.app')

@section('title', 'Панель управления')

@section('content')
    <div class="row">
        <!-- Информация о текущем аккаунте и статусе API -->
        <div class="col-md-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Текущий API-аккаунт: <strong>{{ $currentAccount->name }}</strong></h5>
                        <small class="text-muted">Client ID: {{ $currentAccount->client_id }}</small>
                        <div id="ozon-status" class="mt-2">
                            <span class="spinner-border spinner-border-sm" role="status"></span> Проверка...
                        </div>
                    </div>
                    <button id="checkOzonBtn" class="btn btn-outline-primary">Проверить соединение</button>
                </div>
            </div>
        </div>

        <!-- Статистические карточки -->
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-primary shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Всего заказов</h5>
                    <p class="card-text display-6">{{ $totalOrders }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-success shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Заказов за 7 дней</h5>
                    <p class="card-text display-6">{{ $ordersLast7Days }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-info shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Доставленных заказов</h5>
                    <p class="card-text display-6">{{ $deliveredOrders }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-secondary shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Успешно отправлено</h5>
                    <p class="card-text display-6">{{ $successCount }} / {{ $successCount + $errorCount }}</p>
                </div>
            </div>
        </div>

        <!-- Быстрые действия -->
        <div class="col-md-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">Быстрые действия</div>
                <div class="card-body d-flex gap-3 flex-wrap">
                    <form method="POST" action="{{ route('admin.orders.sync') }}" class="d-inline">
                        @csrf
                        <select name="days" class="form-select d-inline w-auto">
                            <option value="7">За 7 дней</option>
                            <option value="30" selected>За 30 дней</option>
                            <option value="60">За 60 дней</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Запустить синхронизацию заказов</button>
                    </form>
                    <a href="{{ route('admin.messages.buyers') }}" class="btn btn-success">Перейти к рассылке</a>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-info">Товары</a>
                    <a href="{{ route('admin.api_accounts.index') }}" class="btn btn-secondary">API-аккаунты</a>
                </div>
            </div>
        </div>

        <!-- Последние отправки -->
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">Последние отправки</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr><th>Дата</th><th>Заказ</th><th>Получатель</th><th>Статус</th><th>Ошибка</th></tr>
                            </thead>
                            <tbody>
                            @forelse($recentLogs as $log)
                                <tr>
                                    <td>{{ $log->created_at }}</td>
                                    <td>{{ $log->order->posting_number ?? '—' }}</td>
                                    <td>{{ $log->recipient_name ?? '—' }}</td>
                                    <td>
                                        @if($log->status == 'success')
                                            <span class="badge bg-success">Успешно</span>
                                        @else
                                            <span class="badge bg-danger">Ошибка</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->error_text ?? '—' }}</td>
                                </tr>
                            @empty
                                <td><td colspan="5" class="text-center">Нет отправленных сообщений. Создайте рассылку.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <a href="{{ route('admin.messages.history') }}" class="btn btn-link">Полная история</a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function checkOzon() {
                const statusDiv = document.getElementById('ozon-status');
                statusDiv.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Проверка...';
                fetch('{{ route("admin.check.ozon") }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            statusDiv.innerHTML = '<span class="badge bg-success">✓ ' + data.message + '</span>';
                        } else {
                            statusDiv.innerHTML = '<span class="badge bg-danger">✗ ' + data.message + '</span>';
                        }
                    })
                    .catch(error => {
                        statusDiv.innerHTML = '<span class="badge bg-danger">Ошибка запроса: ' + error + '</span>';
                    });
            }
            document.getElementById('checkOzonBtn').addEventListener('click', checkOzon);
            document.addEventListener('DOMContentLoaded', checkOzon);
        </script>
    @endpush
@endsection
