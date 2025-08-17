@extends('app')

@section('title', 'ホーム')

@section('content')
<div class="container py-4 py-md-5">

    {{-- ヒーロー / グリーティング --}}
    <div class="hero rounded-4 p-4 p-md-5 mb-4 shadow-sm">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h1 class="display-6 fw-bold mb-2 text-white">
                    報告書管理システム
                </h1>
                <p class="lead mb-0 text-white-50">
                    ようこそ、<span class="fw-semibold">{{ Auth::user()->name }}</span> さん。<br class="d-none d-md-block">
                    手早く「作成」か「一覧」から始めましょう。
                </p>
            </div>

            {{-- 右側：ログアウトボタン --}}
            @php
                $logoutAction = \Illuminate\Support\Facades\Route::has('logout') ? route('logout') : url('/logout');
            @endphp
            <div class="d-grid d-md-flex gap-2">
                <form method="POST" action="{{ $logoutAction }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-cta">
                        <span class="me-1">↩</span> ログアウト
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- クイックアクション（カード） --}}
    <div class="row g-3 g-md-4">
        <div class="col-12 col-md-6">
            <div class="card h-100 shadow-sm hover-lift rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="badge bg-primary-subtle text-primary-emphasis rounded-pill me-2">新規</div>
                        <h5 class="card-title mb-0">ヒアリングシートの新規作成</h5>
                    </div>
                    <p class="text-muted mb-4">
                        調査日時・依頼人・目的などを入力して、報告書作成の準備を始めます。
                    </p>
                    <a href="{{ route('hearingsheet.index') }}" class="btn btn-primary w-100">
                        <span class="me-2">✨</span> 作成に進む
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card h-100 shadow-sm hover-lift rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="badge bg-success-subtle text-success-emphasis rounded-pill me-2">管理</div>
                        <h5 class="card-title mb-0">既存シートを確認・編集</h5>
                    </div>
                    <p class="text-muted mb-4">
                        作成済みのヒアリングシートを一覧から開き、編集や報告書作成へ進めます。
                    </p>
                    <a href="{{ route('hearingsheet.list') }}" class="btn btn-outline-success w-100">
                        <span class="me-2">📚</span> 一覧を見る
                    </a>
                </div>
            </div>
        </div>

        {{-- ▼ 管理者機能カード（管理者のみ表示） --}}
        @if(Auth::user()?->is_admin)
        <div class="col-12 col-md-6">
            <div class="card h-100 shadow-sm hover-lift rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="badge bg-dark-subtle text-dark rounded-pill me-2">管理者</div>
                        <h5 class="card-title mb-0">管理者機能</h5>
                    </div>
                    <p class="text-muted mb-4">
                        ユーザーの作成・編集（権限付与や名前変更）・削除を行います。
                    </p>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-dark w-100">
                        <span class="me-2">🛠</span> 管理者機能を開く
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- パスワード変更（Fortify: user-password.update） --}}
    <div class="row g-3 g-md-4 mt-1">
        <div class="col-12 col-md-6">
            <div class="card h-100 shadow-sm hover-lift rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="badge bg-warning-subtle text-warning-emphasis rounded-pill me-2">セキュリティ</div>
                        <h5 class="card-title mb-0">パスワード変更</h5>
                    </div>

                    @if (session('status') === 'password-updated')
                        <div class="alert alert-success py-2">パスワードを更新しました。</div>
                    @endif

                    <form method="POST" action="{{ route('user-password.update') }}" id="password-update-form" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label">現在のパスワード</label>
                            <div class="input-group">
                                <input type="password" id="current_password" name="current_password"
                                       class="form-control @error('current_password') is-invalid @enderror" required autocomplete="current-password">
                                <button class="btn btn-outline-secondary" type="button" data-toggle-pw="#current_password">👁️</button>
                                @error('current_password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">新しいパスワード</label>
                            <div class="input-group">
                                <input type="password" id="password" name="password"
                                       class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" data-toggle-pw="#password">👁️</button>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">新しいパスワード（確認）</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password">
                        </div>

                        <button type="submit" class="btn btn-warning w-100" id="pw-submit">
                            <span class="spinner-border spinner-border-sm me-2 d-none" id="pw-spinner" role="status" aria-hidden="true"></span>
                            パスワードを更新
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- 追加スタイル（軽めの装飾） --}}
<style>
/* ヒーローのグラデーション */
.hero {
    background: radial-gradient(1200px 600px at 80% -20%, rgba(255,255,255,.15), transparent),
                linear-gradient(135deg, #4f46e5 0%, #22c55e 100%);
}

/* CTAボタンを指で押しやすく */
.btn-cta { min-width: 240px; }

/* カードのホバー効果（控えめ） */
.hover-lift { transition: transform .18s ease, box-shadow .18s ease; }
.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1.25rem rgba(0,0,0,.08)!important;
}

/* バッジ色（既存トーンに揃え） */
.bg-primary-subtle { background-color: rgba(59,130,246,.12)!important; }
.text-primary-emphasis { color: #1d4ed8!important; }
.bg-success-subtle { background-color: rgba(34,197,94,.12)!important; }
.text-success-emphasis { color: #15803d!important; }
/* 追加：管理者/警告トーン */
.bg-dark-subtle { background-color: rgba(0,0,0,.08)!important; }
.bg-warning-subtle { background-color: rgba(245, 158, 11, .12)!important; }
.text-warning-emphasis { color: #b45309!important; }

/* モバイル最適化 */
@media (max-width: 767.98px) {
    .btn-cta { width: 100%; min-width: 0; }
    .hero .display-6 { font-size: 1.6rem; }
    .hero .lead { font-size: .98rem; }
    .card .btn { padding: .7rem 1rem; }
}
</style>

<script>
  // パスワード表示切替（ボタンに data-toggle-pw="#id" を指定）
  document.querySelectorAll('[data-toggle-pw]').forEach(btn => {
    btn.addEventListener('click', () => {
      const sel = btn.getAttribute('data-toggle-pw');
      const input = document.querySelector(sel);
      if (!input) return;
      const isPw = input.type === 'password';
      input.type = isPw ? 'text' : 'password';
      btn.textContent = isPw ? '🙈' : '👁️';
    });
  });

  // 二重送信防止（スピナー表示）
  const pwForm = document.getElementById('password-update-form');
  if (pwForm) {
    pwForm.addEventListener('submit', () => {
      const btn = document.getElementById('pw-submit');
      const spn = document.getElementById('pw-spinner');
      btn.disabled = true;
      spn.classList.remove('d-none');
    });
  }
</script>
@endsection
