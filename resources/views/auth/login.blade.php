@extends('app')

@section('title', 'ログイン')

@section('content')
<div class="auth-hero py-5 py-md-0">
  <div class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="auth-card card shadow-lg border-0 glass-card p-4 p-md-5" style="max-width: 520px; width: 100%;">
      <div class="text-center mb-4">
        <div class="brand-badge rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
          🔐
        </div>
        <h1 class="h4 fw-bold mb-1">ログイン</h1>
        <p class="text-muted mb-0">アカウントにサインインしてください</p>
      </div>

      @if(session('status'))
        <div class="alert alert-success mb-3">{{ session('status') }}</div>
      @endif

      <form method="POST" action="{{ route('login') }}" novalidate id="login-form">
        @csrf

        <div class="mb-3">
          <label for="email" class="form-label">メールアドレス</label>
          <div class="input-group">
            <span class="input-group-text" id="ig-email" aria-hidden="true">@</span>
            <input
              type="email"
              id="email"
              name="email"
              class="form-control @error('email') is-invalid @enderror"
              value="{{ old('email') }}"
              autocomplete="email"
              autofocus
              required
              aria-describedby="ig-email"
            >
            @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">パスワード</label>
          <div class="input-group">
            <input
              type="password"
              id="password"
              name="password"
              class="form-control @error('password') is-invalid @enderror"
              autocomplete="current-password"
              required
            >
            <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="パスワード表示切替">👁️</button>
            @error('password')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="mb-3 form-switch">
          <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
          <label class="form-check-label small" for="remember">ログイン状態を保持</label>
        </div>

        <button type="submit" class="btn btn-primary w-100" id="login-submit">
          <span class="spinner-border spinner-border-sm me-2 d-none" id="login-spinner" role="status" aria-hidden="true"></span>
          ログイン
        </button>

        {{-- パスワードをお忘れですか？ リンクは不要のため非表示 --}}
      </form>
    </div>
  </div>
</div>

<style>
/* 背景：柔らかいグラデーション＋淡いアクセント */
.auth-hero{
  background:
    radial-gradient(1000px 500px at 90% -10%, rgba(255,255,255,.18), transparent),
    radial-gradient(800px 400px at -10% 110%, rgba(255,255,255,.12), transparent),
    linear-gradient(135deg, #4f46e5 0%, #22c55e 100%);
}

/* ガラス風カード */
.glass-card{
  border-radius: 1.25rem;
  background: rgba(255,255,255,.9);
  backdrop-filter: saturate(160%) blur(8px);
}

/* 丸いバッジ（簡易ロゴ） */
.brand-badge{
  width: 56px; height: 56px;
  background: linear-gradient(180deg, #ffffff, #eef2ff);
  box-shadow: 0 10px 30px rgba(0,0,0,.08), inset 0 1px 0 rgba(255,255,255,.6);
  font-size: 1.4rem;
}

/* 入力まわりを少しだけ“密”に */
.input-group-text{ background: #f8f9fa; }

/* モバイル最適化 */
@media (max-width: 767.98px){
  .glass-card{ padding: 1.25rem !important; }
  .brand-badge{ width: 52px; height: 52px; font-size: 1.3rem; }
}
</style>

<script>
  // パスワード表示切替
  document.getElementById('togglePassword')?.addEventListener('click', function(){
    const input = document.getElementById('password');
    const isPw = input.type === 'password';
    input.type = isPw ? 'text' : 'password';
    this.textContent = isPw ? '🙈' : '👁️';
  });

  // 二重送信防止（軽いスピナー）
  document.getElementById('login-form')?.addEventListener('submit', function(){
    const btn = document.getElementById('login-submit');
    const spn = document.getElementById('login-spinner');
    btn.disabled = true;
    spn.classList.remove('d-none');
  });
</script>
@endsection
