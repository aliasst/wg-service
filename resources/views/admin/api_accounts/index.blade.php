@extends('layouts.app')

@section('title', 'API-аккаунты Ozon')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">API-аккаунты</h5>
            <a href="{{ route('admin.api_accounts.create') }}" class="btn btn-primary btn-sm">+ Добавить аккаунт</a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Client ID</th>
                    <th>Активен</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                @forelse($accounts as $account)
                    <tr>
                        <td>{{ $account->id }}</td>
                        <td>{{ $account->name }}</td>
                        <td>{{ $account->client_id }}</td>
                        <td>{!! $account->is_active ? '<span class="badge bg-success">Да</span>' : '<span class="badge bg-danger">Нет</span>' !!}</td>
                        <td>
                            <a href="{{ route('admin.api_accounts.edit', $account) }}" class="btn btn-sm btn-warning">Изменить</a>
                            <form action="{{ route('admin.api_accounts.destroy', $account) }}" method="POST" class="d-inline" onsubmit="return confirm('Удалить аккаунт?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Удалить</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">Нет API-аккаунтов. Добавьте первый.</td></tr>
                @endforelse
            </table>
            </table>
        </div>
    </div>
@endsection
