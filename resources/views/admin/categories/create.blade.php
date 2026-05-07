@extends('layouts.app')

@section('title', 'Добавить категорию')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>Новая категория</h5>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.categories.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Внутреннее название *</label>
                    <input type="text" name="internal_name" class="form-control" value="{{ old('internal_name') }}" required>
                    <small class="text-muted">Пример: Стиральные машины, Посудомоечные машины</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">ID категорий Ozon (через запятую) *</label>
                    <textarea name="ozon_category_ids" class="form-control" rows="3" required>{{ old('ozon_category_ids') }}</textarea>
                    <small class="text-muted">Например: 123456, 789012, 345678</small>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" value="1" checked>
                    <label class="form-check-label">Активна</label>
                </div>

                <button type="submit" class="btn btn-primary">Сохранить</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Отмена</a>
            </form>
        </div>
    </div>
@endsection
