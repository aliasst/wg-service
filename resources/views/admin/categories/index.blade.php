@extends('layouts.app')

@section('title', 'Управление категориями')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Категории для рассылки</h5>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">+ Добавить категорию</a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-bordered">
                <thead>
                <tr><th>ID</th><th>Внутреннее название</th><th>ID категорий Ozon</th><th>Активна</th><th>Действия</th></tr>
                </thead>
                <tbody>
                @forelse($categories as $cat)
                    <tr>
                        <td>{{ $cat->id }}</td>
                        <td>{{ $cat->internal_name }}</td>
                        <td>
                            @foreach($cat->ozon_category_ids as $id)
                                <span class="badge bg-secondary">{{ $id }}</span>
                            @endforeach
                        </td>
                        <td>{!! $cat->is_active ? '<span class="badge bg-success">Да</span>' : '<span class="badge bg-danger">Нет</span>' !!}</td>
                        <td>
                            <a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-sm btn-warning">Изменить</a>
                            <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('Удалить категорию?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Удалить</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">Нет категорий. Добавьте первую.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
