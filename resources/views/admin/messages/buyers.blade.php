@extends('layouts.app')

@section('title', 'Рассылка сообщений')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>Шаг 1. Выберите категорию товаров</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.messages.buyers') }}" class="row g-3">
                <div class="col-md-6">
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Выберите категорию --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected($selectedCategoryId == $cat->id)>{{ $cat->internal_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Показать покупателей</button>
                </div>
            </form>
        </div>
    </div>

    @if($selectedCategoryId && $buyers->count())
        <div class="card mt-4">
            <div class="card-header">
                <h5>Шаг 2. Выберите получателей и составьте сообщение</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.messages.send') }}">
                    @csrf
                    <input type="hidden" name="category_id" value="{{ $selectedCategoryId }}">

                    <div class="mb-3">
                        <label class="form-label">Заголовок (не более 60 символов)</label>
                        <input type="text" name="header" class="form-control" maxlength="60" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Текст сообщения</label>
                        <textarea name="body" class="form-control" rows="5" required></textarea>
                        <small class="text-muted">Поддерживаются эмодзи. Заголовок и текст будут разделены двумя переводами строки.</small>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                        <tr><th><input type="checkbox" id="selectAll"></th><th>Имя покупателя</th><th>Номер заказа</th><th>Телефон</th></tr>
                        </thead>
                        <tbody>
                        @foreach($buyers as $order)
                            <tr>
                                <td><input type="checkbox" name="order_ids[]" value="{{ $order->id }}"></td>
                                <td>{{ $order->customer_name ?? 'Не указан' }}</td>
                                <td>{{ $order->posting_number }}</td>
                                <td>{{ $order->customer_phone ?? '—' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-success">Отправить выбранным</button>
                </form>
            </div>
        </div>

        <script>
            document.getElementById('selectAll')?.addEventListener('change', function(e) {
                document.querySelectorAll('input[name="order_ids[]"]').forEach(cb => cb.checked = e.target.checked);
            });
        </script>
    @elseif($selectedCategoryId)
        <div class="alert alert-warning mt-4">Нет покупателей, соответствующих выбранной категории.</div>
    @endif
@endsection
