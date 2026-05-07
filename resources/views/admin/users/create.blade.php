@extends('layouts.app')

@section('title', 'Добавить пользователя')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>Новый пользователь</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Логин</label>
                    <input type="text" name="login" class="form-control @error('login') is-invalid @enderror" value="{{ old('login') }}" required>
                    @error('login')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Пароль</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Роль</label>
                    <select name="role" class="form-control">
                        <option value="manager">Менеджер</option>
                        <option value="admin">Администратор</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Сохранить</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Отмена</a>
            </form>
        </div>
    </div>
@endsection
