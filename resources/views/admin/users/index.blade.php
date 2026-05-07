@extends('layouts.app')

@section('title', 'Пользователи')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Пользователи системы</h5>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">+ Добавить</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                <tr><th>ID</th><th>Логин</th><th>Роль</th><th>Действия</th></tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->login }}</td>
                        <td>{{ $user->role === 'admin' ? 'Администратор' : 'Менеджер' }}</td>
                        <td>
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning">Изменить</a>
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')">Удалить</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
