@extends('layouts.app')

@section('title', 'Рассылка сообщений')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>Шаг 1. Выберите категорию товаров</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.messages.buyers') }}" class="row g-3 mb-4">
                <div class="col-md-4">
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Выберите категорию --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected($selectedCategoryId == $cat->id)>{{ $cat->internal_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="paid_days" class="form-select">
                        <option value="">Любая дата оплаты</option>
                        <option value="1" @selected(request('paid_days') == 1)>За последние 24 часа</option>
                        <option value="3" @selected(request('paid_days') == 3)>За последние 3 дня</option>
                        <option value="7" @selected(request('paid_days') == 7)>За последние 7 дней</option>
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
                <form method="POST" action="{{ route('admin.messages.send') }}" id="sendForm">
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

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAllBtn">Выбрать всех</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllBtn">Снять выделение</button>
                        </div>
                        <div>
                            <strong>Выбрано: <span id="selectedCount">0</span></strong>
                        </div>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>Имя покупателя</th>
                            <th>Номер заказа</th>
                            <th>Телефон</th>
                            <th>Тип заказа</th>
                            <th>Статус отправки</th>
                            <th>Время с оплаты</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($buyers as $order)
                            <tr>
                                <td><input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="buyer-checkbox"></td>
                                <td>{{ $order->customer_name ?? 'Не указан' }}</td>
                                <td>{{ $order->posting_number }}</td>
                                <td>{{ $order->customer_phone ?? '—' }}</td>
                                <td>
                                    @if($order->order_type === 'fbs')
                                        <span class="badge bg-primary">FBS</span>
                                    @elseif($order->order_type === 'fbo')
                                        <span class="badge bg-secondary">FBO</span>
                                    @else
                                        <span class="badge bg-light text-dark">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $lastLog = $order->messageLogs->first();
                                    @endphp
                                    @if($lastLog)
                                        @if($lastLog->status === 'success')
                                            <span class="badge bg-success">Отправлено</span>
                                        @else
                                            <span class="badge bg-danger" title="{{ $lastLog->error_text }}">Ошибка</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Не отправлялось</span>
                                    @endif
                                </td>
                                <td>
                                    @if($order->payment_date)
                                        <span class="{{ $order->payment_days > 3 ? 'text-danger' : 'text-muted' }}">
            {{ $order->payment_interval }}
        </span>
                                        @if($order->payment_days > 3)
                                            <i class="fas fa-exclamation-triangle text-danger" title="Истёк срок для открытия чата (более 3 дней)"></i>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-success mt-3">Отправить выбранным</button>
                </form>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const checkboxes = document.querySelectorAll('.buyer-checkbox');
                const selectAllCheckbox = document.getElementById('selectAll');
                const selectedCountSpan = document.getElementById('selectedCount');
                const selectAllBtn = document.getElementById('selectAllBtn');
                const deselectAllBtn = document.getElementById('deselectAllBtn');

                function updateSelectedCount() {
                    const checked = document.querySelectorAll('.buyer-checkbox:checked').length;
                    selectedCountSpan.textContent = checked;
                }

                function setAllCheckboxes(checked) {
                    checkboxes.forEach(cb => cb.checked = checked);
                    if (selectAllCheckbox) selectAllCheckbox.checked = checked;
                    updateSelectedCount();
                }

                // Обновление счетчика при клике на любой чекбокс
                checkboxes.forEach(cb => {
                    cb.addEventListener('change', updateSelectedCount);
                });

                // Чекбокс "Выделить все"
                if (selectAllCheckbox) {
                    selectAllCheckbox.addEventListener('change', function() {
                        setAllCheckboxes(this.checked);
                    });
                }

                // Кнопка "Выбрать всех"
                if (selectAllBtn) {
                    selectAllBtn.addEventListener('click', function() {
                        setAllCheckboxes(true);
                    });
                }

                // Кнопка "Снять выделение"
                if (deselectAllBtn) {
                    deselectAllBtn.addEventListener('click', function() {
                        setAllCheckboxes(false);
                    });
                }

                // Инициализация счетчика
                updateSelectedCount();
            });
        </script>
    @elseif($selectedCategoryId)
        <div class="alert alert-warning mt-4">Нет покупателей, соответствующих выбранной категории.</div>
    @endif
@endsection
