@extends('app')

@section('title', 'ヒアリングシート一覧')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">ヒアリングシート一覧</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- 画面上部アナウンス（トースト） --}}
    <div id="copy-toast" class="alert app-toast d-none" role="status" aria-live="polite"></div>

    {{-- ▼ 検索＆並び替えフォーム（GET） --}}
    <form method="GET" class="mb-3 mb-md-4">
    <div class="row g-2 g-md-3 align-items-end">
        <div class="col-12 col-md-5 col-lg-4">
        <label class="form-label mb-1">キーワード</label>
        <input type="search" name="q" class="form-control"
                value="{{ request('q') }}"
                placeholder="ID / 種類 / 依頼人 / 担当者 / 目的">
        </div>

        @if(Auth::user()?->is_admin)
        <div class="col-12 col-md-3 col-lg-3">
            <label class="form-label mb-1">担当者（管理者）</label>
            <select name="staff" class="form-select">
            <option value="">すべて</option>
            @foreach(($staffOptions ?? []) as $name)
                <option value="{{ $name }}" {{ request('staff')===$name ? 'selected' : '' }}>
                {{ $name }}
                </option>
            @endforeach
            </select>
        </div>
        @endif

        <div class="col-6 col-md-2 col-lg-2">
        <label class="form-label mb-1">並び替え</label>
        <select name="sort" class="form-select">
            <option value="interview_datetime" {{ request('sort','interview_datetime')==='interview_datetime'?'selected':'' }}>日時</option>
            <option value="created_at" {{ request('sort')==='created_at'?'selected':'' }}>作成日</option>
            <option value="id" {{ request('sort')==='id'?'selected':'' }}>ID</option>
        </select>
        </div>

        <div class="col-6 col-md-2 col-lg-1">
        <label class="form-label mb-1"></label>
        <select name="dir" class="form-select">
            <option value="desc" {{ request('dir','desc')==='desc'?'selected':'' }}>降順</option>
            <option value="asc"  {{ request('dir')==='asc' ?'selected':'' }}>昇順</option>
        </select>
        </div>

        <div class="col-6 col-md-2 col-lg-1 d-grid">
        <button type="submit" class="btn btn-primary">検索</button>
        </div>
        <div class="col-6 col-md-2 col-lg-1 d-grid">
        <a href="{{ route('hearingsheet.list') }}" class="btn btn-outline-secondary">リセット</a>
        </div>
    </div>
    </form>

    {{-- ▼ デスクトップ／タブレット（md以上）：モダンテーブル表示 --}}
    <div class="modern-table-wrapper d-none d-md-block">
        <div class="table-responsive">
            <table class="table modern-table align-middle table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>日時</th>
                        <th>調査種類</th>
                        <th>依頼人</th>
                        <th>担当者</th>
                        <th style="min-width:260px;">調査目的</th>
                        <th style="width:110px;">編集</th>
                        <th style="width:140px;">報告書作成</th>
                        <th style="width:120px;">URLコピー</th>
                        <th style="width:120px;">キーコピー</th>
                        <th style="width:100px;">削除</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sheets as $sheet)
                        @php
                            $report    = $sheet->report ?? null;
                            $hasUrl    = $report && !empty($report->report_key);
                            $hasKey    = $report && !empty($report->access_key);
                            $publicUrl = $hasUrl ? route('report.public.form', $report->report_key) : '';
                            $accessKey = $hasKey ? $report->access_key : '';
                        @endphp
                        <tr>
                            <td class="cell-compact">#{{ $sheet->id }}</td>
                            <td class="cell-compact" style="white-space: nowrap;">{{ $sheet->interview_datetime }}</td>
                            <td class="cell-compact">{{ $sheet->investigation_type }}</td>
                            <td class="cell-compact">{{ $sheet->client_name }}</td>
                            <td class="cell-compact">{{ $sheet->staff_name }}</td>
                            <td title="{{ $sheet->purpose }}">
                                <div class="text-truncate" style="max-width: 420px;">{{ $sheet->purpose }}</div>
                            </td>
                            <td>
                                <a href="{{ route('hearingsheet.edit', $sheet->id) }}" class="btn btn-sm btn-outline-primary w-100">編集</a>
                            </td>
                            <td>
                                <a href="{{ route('report.create', ['id' => $sheet->id]) }}" class="btn btn-sm btn-primary w-100">報告書作成</a>
                            </td>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-sm {{ $hasUrl ? 'btn-outline-secondary copy-btn' : 'btn-secondary' }} w-100"
                                    data-copy-text="{{ $publicUrl }}"
                                    data-copy-kind="url"
                                    {{ $hasUrl ? '' : 'disabled' }}
                                    title="{{ $hasUrl ? '公開URLをコピー' : '報告書未作成またはキー未発行' }}"
                                >URLコピー</button>
                            </td>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-sm {{ $hasKey ? 'btn-outline-secondary copy-btn' : 'btn-secondary' }} w-100"
                                    data-copy-text="{{ $accessKey }}"
                                    data-copy-kind="key"
                                    {{ $hasKey ? '' : 'disabled' }}
                                    title="{{ $hasKey ? 'アクセスキーをコピー' : '報告書未作成またはキー未発行' }}"
                                >キーコピー</button>
                            </td>
                            <td>
                                <form action="{{ route('hearingsheet.destroy', $sheet->id) }}" method="POST"
                                      onsubmit="return confirm('ID:{{ $sheet->id }} を削除します。よろしいですか？この操作は元に戻せません。');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">削除</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ▼ モバイル（md未満）：カード表示 --}}
    <div class="d-md-none">
        <div class="d-flex flex-column gap-3">
            @foreach($sheets as $sheet)
                @php
                    $report    = $sheet->report ?? null;
                    $hasUrl    = $report && !empty($report->report_key);
                    $hasKey    = $report && !empty($report->access_key);
                    $publicUrl = $hasUrl ? route('report.public.form', $report->report_key) : '';
                    $accessKey = $hasKey ? $report->access_key : '';
                @endphp

                <div class="card shadow-sm modern-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="fw-bold">#{{ $sheet->id }}</div>
                            <div class="text-muted small" style="white-space:nowrap;">{{ $sheet->interview_datetime }}</div>
                        </div>

                        <div class="mb-1"><span class="text-muted small">調査種類：</span>{{ $sheet->investigation_type }}</div>
                        <div class="mb-1"><span class="text-muted small">依頼人：</span>{{ $sheet->client_name }}</div>
                        <div class="mb-1"><span class="text-muted small">担当者：</span>{{ $sheet->staff_name }}</div>
                        <div class="mb-2">
                            <span class="text-muted small">目的：</span>
                            <span style="word-break: break-word;">{{ \Illuminate\Support\Str::limit($sheet->purpose, 80) }}</span>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="{{ route('hearingsheet.edit', $sheet->id) }}" class="btn btn-outline-primary btn-sm">編集</a>
                            <a href="{{ route('report.create', ['id' => $sheet->id]) }}" class="btn btn-primary btn-sm">報告書作成</a>

                            <div class="d-flex gap-2">
                                <button
                                    type="button"
                                    class="btn btn-sm flex-fill {{ $hasUrl ? 'btn-outline-secondary copy-btn' : 'btn-secondary' }}"
                                    data-copy-text="{{ $publicUrl }}"
                                    data-copy-kind="url"
                                    {{ $hasUrl ? '' : 'disabled' }}
                                    title="{{ $hasUrl ? '公開URLをコピー' : '報告書未作成またはキー未発行' }}"
                                >URLコピー</button>

                                <button
                                    type="button"
                                    class="btn btn-sm flex-fill {{ $hasKey ? 'btn-outline-secondary copy-btn' : 'btn-secondary' }}"
                                    data-copy-text="{{ $accessKey }}"
                                    data-copy-kind="key"
                                    {{ $hasKey ? '' : 'disabled' }}
                                    title="{{ $hasKey ? 'アクセスキーをコピー' : '報告書未作成またはキー未発行' }}"
                                >キーコピー</button>
                            </div>

                            <form action="{{ route('hearingsheet.destroy', $sheet->id) }}" method="POST"
                                  onsubmit="return confirm('ID:{{ $sheet->id }} を削除します。よろしいですか？この操作は元に戻せません。');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100">削除</button>
                            </form>
                        </div>
                    </div>
                </div>

            @endforeach
        </div>
    </div>

    <div class="mt-3">
        {{ $sheets->links() }}
    </div>
</div>

{{-- コピー＆トースト用スクリプト --}}
<script>
function showAnnouncement(message, type = 'success', duration = 1800) {
    const el = document.getElementById('copy-toast');
    if (!el) return;

    el.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning', 'alert-info');
    el.classList.add('alert-' + type);
    el.textContent = message;

    clearTimeout(window._copyToastTimer);
    window._copyToastTimer = setTimeout(() => {
        el.classList.add('d-none');
        el.classList.remove('alert-' + type);
        el.textContent = '';
    }, duration);
}

document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.copy-btn');
    if (!btn) return;

    const text = btn.dataset.copyText || '';
    const kind = btn.dataset.copyKind || 'url';
    if (!text) return;

    const prevDisabled = btn.disabled;
    btn.disabled = true;

    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);
        } else {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
        }
        showAnnouncement(kind === 'key' ? 'アクセスキーをコピーしました。' : '公開URLをコピーしました。', 'success');
    } catch (err) {
        console.error(err);
        showAnnouncement(kind === 'key' ? 'アクセスキーのコピーに失敗しました。' : '公開URLのコピーに失敗しました。', 'danger');
    } finally {
        setTimeout(() => { btn.disabled = prevDisabled; }, 300);
    }
});
</script>

{{-- スタイル（PCテーブルをモダン化 & モバイル微調整） --}}
<style>
/* トースト：共通（既存と同様） */
.app-toast{
  position: fixed;
  top: calc(env(safe-area-inset-top, 0) + 8px);
  left: 50%;
  transform: translateX(-50%);
  width: clamp(240px, 92vw, 560px);
  z-index: 1050;
}

/* ==== PC用：モダンテーブル ==== */
.modern-table-wrapper{
  border: 1px solid rgba(0,0,0,.08);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 .4rem 1.2rem rgba(0,0,0,.06);
  background: #fff;
}
.modern-table{
  margin-bottom: 0;
}
.modern-table thead th{
  position: sticky;
  top: 0;
  z-index: 2;
  background: linear-gradient(180deg, rgba(255,255,255,.9), rgba(255,255,255,.85));
  backdrop-filter: saturate(120%) blur(4px);
  border-bottom: 1px solid rgba(0,0,0,.06);
  font-weight: 700;
  color: #334;
}
.modern-table td, .modern-table th{
  border: 0;
  vertical-align: middle;
}
.modern-table tbody tr{
  transition: background-color .15s ease, box-shadow .2s ease, transform .06s ease;
}
.modern-table tbody tr:nth-child(odd){
  background-color: rgba(0,0,0,.015);
}
.modern-table tbody tr:hover{
  background-color: rgba(0,0,0,.03);
  transform: translateY(-1px);
  box-shadow: 0 .2rem .6rem rgba(0,0,0,.05);
}
.cell-compact{ white-space: nowrap; }

/* ==== モバイルカードのちょいオシャ ==== */
.modern-card{
  border: 1px solid rgba(0,0,0,.06);
  border-radius: 14px;
}
@media (max-width: 767.98px) {
  .card .text-muted.small { font-size: .8rem; }
  .card .btn { padding: .6rem .8rem; }
  .card .btn.btn-sm { font-size: .95rem; }
}
</style>
@endsection