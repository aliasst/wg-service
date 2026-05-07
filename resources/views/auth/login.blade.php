<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Авторизация | Ozon Bot</title>
    <!-- Bootstrap 5 CDN -->
    <link rel="stylesheet" href="{{ asset('assets/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.min.css') }}"/>
    <style>
        body {
            background: #f0f2f5;
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .card {
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
        }
        .btn-primary {
            background-color: #1c5a6a;
            border-color: #1c5a6a;
        }
        .btn-primary:hover {
            background-color: #0f3f4b;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header bg-white text-center border-0 pt-4">
                    <h4 class="mb-0">Вход в систему</h4>
                    <small class="text-muted">Ozon Chat Bot</small>
                </div>
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="login" class="form-label">Логин</label>
                            <input type="text" class="form-control @error('login') is-invalid @enderror"
                                   id="login" name="login" value="{{ old('login') }}" autofocus>
                            @error('login')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password">
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Запомнить меня</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Войти</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
