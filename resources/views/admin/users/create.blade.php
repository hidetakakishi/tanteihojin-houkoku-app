@extends('app')
@section('title', '管理者 - ユーザー作成')

@section('content')
<div class="container mt-5">
  <h2 class="mb-4">ユーザー作成</h2>

  @if($errors->any())
    <div class="alert alert-danger">
      @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('admin.users.store') }}">
    @csrf
    <div class="mb-3">
      <label class="form-label">名前</label>
      <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
    </div>
    <div class="mb-3">
      <label class="form-label">メール</label>
      <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
    </div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">パスワード</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">パスワード（確認）</label>
        <input type="password" name="password_confirmation" class="form-control" required>
      </div>
    </div>
    <div class="form-check form-switch mt-3">
      <input class="form-check-input" type="checkbox" name="is_admin" id="is_admin" value="1" {{ old('is_admin')?'checked':'' }}>
      <label class="form-check-label" for="is_admin">管理者にする</label>
    </div>

    <div class="d-flex gap-2 mt-4">
      <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">戻る</a>
      <button class="btn btn-primary">作成</button>
    </div>
  </form>
</div>
@endsection
