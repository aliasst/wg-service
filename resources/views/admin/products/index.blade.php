@extends('layouts.app')

@section('title', 'Товары')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>Уникальные товары</h5>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Offer ID</th>
                        <th>Название товара</th>
                        <th>Категория Ozon (ID)</th>
                        <th>Внутренняя категория</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($products as $product)
                        @php
                            $hasInternal = !is_null($product->internal_name);
                        @endphp
                        <tr>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->offer_id ?? '—' }}</td>
                            <td>{{ $product->product_name }}</td>
                            <td>{{ $product->category_id ?? '—' }}</td>
                            <td class="{{ !$hasInternal ? 'table-danger' : '' }}">
                                {{ $hasInternal ? $product->internal_name : 'Не назначена' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Нет товаров. Запустите синхронизацию заказов.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
