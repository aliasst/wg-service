@extends('layouts.app')

@section('title', 'Редактировать API-аккаунт')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>Редактирование: {{ $account->name }}</h5>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.api_accounts.update', $account) }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Название магазина *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $account->name) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Client ID *</label>
                    <input type="text" name="client_id" class="form-control" value="{{ old('client_id', $account->client_id) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">API Key (оставьте пустым, если не меняете)</label>
                    <input type="text" name="api_key" class="form-control" placeholder="Введите новый ключ, если требуется смена">
                    <small class="text-muted">Ключ хранится в зашифрованном виде. Если не менять – оставьте поле пустым.</small>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" value="1" @checked(old('is_active', $account->is_active))>
                    <label class="form-check-label">Активен</label>
                </div>

                <button type="submit" class="btn btn-primary">Обновить</button>
                <a href="{{ route('admin.api_accounts.index') }}" class="btn btn-secondary">Назад</a>
            </form>
        </div>
    </div>
@endsection
