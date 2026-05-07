@extends('layouts.app')

@section('title', 'Редактировать пользователя')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>Редактирование: {{ $user->login }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Логин</label>
                    <input type="text" name="login" class="form-control @error('login') is-invalid @enderror" value="{{ old('login', $user->login) }}" required>
                    @error('login')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Новый пароль (оставьте пустым, если не менять)</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Роль</label>
                    <select name="role" class="form-control">
                        <option value="manager" @selected($user->role === 'manager')>Менеджер</option>
                        <option value="admin" @selected($user->role === 'admin')>Администратор</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Обновить</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Назад</a>
            </form>
        </div>
    </div>
@endsection
