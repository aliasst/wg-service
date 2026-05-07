@extends('layouts.app')

@section('title', 'Добавить API-аккаунт')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>Новый API-аккаунт</h5>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.api_accounts.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Название магазина *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Client ID *</label>
                    <input type="text" name="client_id" class="form-control" value="{{ old('client_id') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">API Key *</label>
                    <input type="text" name="api_key" class="form-control" value="{{ old('api_key') }}" required>
                    <small class="text-muted">Ключ будет зашифрован в базе данных.</small>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" value="1" checked>
                    <label class="form-check-label">Активен</label>
                </div>

                <button type="submit" class="btn btn-primary">Сохранить</button>
                <a href="{{ route('admin.api_accounts.index') }}" class="btn btn-secondary">Отмена</a>
            </form>
        </div>
    </div>
@endsection
