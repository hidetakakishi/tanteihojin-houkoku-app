@extends('app')

@section('title', 'パスワード再設定')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">パスワード再設定</h2>
    <form method="POST" action="{{ route('password.email') }}">
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

        <button type="submit" class="btn btn-primary">パスワード再設定リンクを送信</button>
    </form>
</div>
@endsection
