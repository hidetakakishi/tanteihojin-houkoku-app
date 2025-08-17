@extends('app')
@section('title', '管理者 - ユーザー編集')

@section('content')
<div class="container mt-5">
  <h2 class="mb-4">ユーザー編集</h2>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('admin.users.update', $user) }}">
    @csrf @method('PUT')

    <div class="mb-3">
      <label class="form-label">名前</label>
      <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
    </div>
    <div class="mb-3">
      <label class="form-label">メール</label>
      <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
    </div>

    {{-- 変更スイッチ --}}
    <div class="form-check form-switch mt-3">
    <input class="form-check-input" type="checkbox" id="change_password" name="change_password" value="1">
    <label class="form-check-label" for="change_password">パスワードを変更する</label>
    </div>

    {{-- パスワード欄（初期は無効・非表示） --}}
    <div id="password_fields" class="row g-3 align-items-end mt-1" style="display:none;">
    <div class="col-md-6">
        <label class="form-label">パスワード（変更時のみ）</label>
        <input type="password" name="password" class="form-control" autocomplete="new-password" disabled>
    </div>
    <div class="col-md-6">
        <label class="form-label">パスワード（確認）</label>
        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password" disabled>
    </div>
    </div>

    <div class="form-check form-switch mt-3">
      <input class="form-check-input" type="checkbox" name="is_admin" id="is_admin" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_admin">管理者にする</label>
    </div>

    <div class="d-flex gap-2 mt-4">
      <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">戻る</a>
      <button class="btn btn-primary">更新</button>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const sw = document.getElementById('change_password');
  const box = document.getElementById('password_fields');
  const toggle = () => {
    const on = sw.checked;
    box.style.display = on ? '' : 'none';
    box.querySelectorAll('input').forEach(i => {
      i.disabled = !on;
      if (!on) i.value = '';
    });
  };
  sw.addEventListener('change', toggle);
  toggle(); // 初期適用
});
</script>
@endsection
