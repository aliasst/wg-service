@extends('layouts.app')

@section('title', 'История отправок')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>Отправленные сообщения</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                <tr><th>ID</th><th>Категория</th><th>Заголовок</th><th>Кто отправил</th><th>Дата</th><th>Статус</th><th>Действия</th></tr>
                </thead>
                <tbody>
                @foreach($messages as $msg)
                    <tr>
                        <td>{{ $msg->id }}</td>
                        <td>{{ $msg->categoryMapping->internal_name ?? '—' }}</td>
                        <td>{{ $msg->header }}</td>
                        <td>{{ $msg->user->login ?? '—' }}</td>
                        <td>{{ $msg->created_at }}</td>
                        <td>
                            <a href="{{ route('admin.messages.logs', $msg->id) }}" class="btn btn-sm btn-info">Логи</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{ $messages->links() }}
        </div>
    </div>
@endsection
