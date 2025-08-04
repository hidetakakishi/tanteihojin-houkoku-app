@extends('app')

@section('title', 'ログイン')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">ログイン</h2>
    <form method="POST" action="{{ route('login') }}">
        @csrf

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @error('email')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        <div class="mb-3">
            <label for="email" class="form-label">メールアドレス</label>
            <input type="email" name="email" class="form-control" required autofocus>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">パスワード</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="remember" class="form-check-input" id="remember">
            <label class="form-check-label" for="remember">ログイン状態を保持</label>
        </div>

        <button type="submit" class="btn btn-primary">ログイン</button>

        <div class="mt-3">
            <a href="{{ route('password.request') }}">パスワードをお忘れですか？</a>
        </div>
    </form>
</div>
@endsection
