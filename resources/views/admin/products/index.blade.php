@extends('layouts.app')

@section('title', 'Товары')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>Уникальные товары (магазин: {{ app('currentApiAccount')->name ?? '—' }})</h5>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($products->isEmpty())
                <div class="alert alert-warning">Нет товаров для текущего магазина. Запустите синхронизацию заказов.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr><th>SKU</th><th>Offer ID</th><th>Название товара</th><th>Категория Ozon (ID)</th><th>Внутренняя категория</th><th>Действия</th></tr>
                        </thead>
                        <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>{{ $product->sku }}</td>
                                <td>{{ $product->offer_id ?? '—' }}</td>
                                <td>{{ $product->product_name }}</td>
                                <td>{{ $product->category_id ?? '—' }}</td>
                                <td class="{{ is_null($product->internal_name) ? 'table-danger' : '' }}">
                                    {{ $product->internal_name ?? 'Не назначена' }}
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary edit-category"
                                            data-sku="{{ $product->sku }}"
                                            data-current="{{ $product->category_mapping_id ?? '' }}"
                                            data-bs-toggle="modal" data-bs-target="#categoryModal">
                                        Назначить
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Модальное окно для назначения категории (оставляем как есть) -->
    ...
@endsection
