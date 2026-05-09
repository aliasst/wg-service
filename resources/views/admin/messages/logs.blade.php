@extends('layouts.app')

@section('title', 'Логи отправки')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>Логи для сообщения #{{ $message->id }} ({{ $message->header }})</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                <tr><th>Заказ</th><th>Получатель</th><th>Дата</th><th>Статус</th><th>Ошибка</th></tr>
                </thead>
                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->order->posting_number ?? '—' }}</td>
                        <td>{{ $log->recipient_name }}</td>
                        <td>{{ $log->sent_at }}</td>
                        <td>{!! $log->status === 'success' ? '<span class="badge bg-success">Успех</span>' : '<span class="badge bg-danger">Ошибка</span>' !!}</td>
                        <td>{{ $log->error_text ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">Нет логов</td></tr>
                @endforelse
                </tbody>
            </table>
            <a href="{{ route('admin.messages.history') }}" class="btn btn-secondary">Назад</a>
        </div>
    </div>
@endsection
