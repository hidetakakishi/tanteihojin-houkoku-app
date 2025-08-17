@extends('app')

@section('title', '報告書作成')

@section('style')
<style>
/* --- 共通の軽い整形 --- */
.outer-content, .inner-content {
    border-radius: .75rem;
}
@media (min-width: 769px){
    .outer-content { background: #fff; }
    .inner-content { background: #fafafa; }
}

/* --- モバイル最適化（<=768px） --- */
@media (max-width: 768px) {
    /* ベース文字サイズを少しだけ上げる（過度に大きくしない） */
    body, input, textarea, label, button, .container {
        font-size: 1.08rem !important;
        line-height: 1.5;
    }
    h2, h4, h5 {
        font-size: 1.2rem !important;
        margin-bottom: .75rem;
    }

    /* 余白・カード感・影 */
    .container {
        padding-left: .75rem;
        padding-right: .75rem;
    }
    .outer-content, .inner-content {
        border-color: rgba(0,0,0,.08) !important;
        box-shadow: 0 .25rem .75rem rgba(0,0,0,.06);
        padding: .9rem !important;
    }

    /* 入力のタップしやすさ */
    .form-control, .form-select, .btn {
        min-height: 44px;
        font-size: 1.02rem;
    }

    /* ボタンは基本フル幅 */
    .btn, .btn-sm {
        width: 100%;
    }

    /* 行間＆下マージンを少し増やす */
    .form-control { margin-bottom: .9rem; }

    /* 画像/動画は横幅に合わせて可変 */
    .preview-area img {
        max-width: 100%;
        height: auto;
    }
    video {
        width: 100% !important;
        height: auto !important;
        max-height: 300px;
        border-radius: .5rem;
    }

    /* 事前情報の画像縮小 */
    #preinfo-section img.img-thumbnail {
        max-height: 120px;
        height: auto;
        width: 100%;
        object-fit: cover;
    }

    /* 左右2カラムは縦積み */
    .col-md-6, .col-md-4, .col-md-2 { margin-bottom: .75rem; }
    .text-end { text-align: center !important; }
}

/* 既存の「.btn { width:100% }」を上書きして横並びを実現 */
@media (max-width: 768px) {
  .btn-row-mobile { min-width: 0; }
  .btn-row-mobile .btn {
    width: auto !important;   /* ← 100%指定を打ち消す */
    flex: 1 1 0;              /* ← 等幅（50:50）にする */
    min-width: 0;             /* ← 折り返し防止 */
  }
}

/* デスクトップでは従来どおり右寄せ等にしたい場合（任意） */
@media (min-width: 769px) {
  .btn-row-mobile { justify-content: flex-end; }
  .btn-row-mobile .btn { width: auto; min-width: 160px; }
}

@media (max-width: 768px) {
  /* プレビュー・保存を横並び等幅に */
  .btn-row-mobile .btn {
    width: auto !important;   /* 既存の100%指定を打ち消し */
    flex: 1 1 0;              /* 等幅 */
    min-width: 0;
  }
}

/* ラベル等の小さいアクセント */
label { font-weight: 600; }

/* 動画ブロックの見た目 */
.video-block {
    background: #fff;
    border: 1px solid rgba(0,0,0,.08);
    border-radius: .75rem;
    padding: .75rem;
}
.video-block .video-title {
    font-weight: 700;
    margin-bottom: .25rem;
}
</style>
@endsection

@section('content')
<div class="container mt-5 pb-5">
    <h2 class="mb-4 text-center">報告書入力</h2>

    @if (session('success'))
        <div class="alert alert-success mb-3">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-3">
            @foreach ($errors->all() as $message)
                <div>{{ $message }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('report.store') }}" enctype="multipart/form-data">
        @csrf

        <input type="hidden" name="hearing_sheet_id" value="{{ $hearing->id }}">

        {{-- 基本情報 --}}
        <div class="card shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <div class="row mb-3 g-3">
                    <div class="col-md-6 col-12">
                        <label>日時：</label>
                        <input type="datetime-local" name="report_datetime" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="col-md-6 col-12">
                        <label>調査種類：</label>
                        <input type="text" class="form-control" value="{{ $hearing->investigation_type }}" readonly>
                    </div>
                </div>

                <div class="row mb-3 g-3">
                    <div class="col-md-6 col-12">
                        <label>依頼人氏名：</label>
                        <input type="text" class="form-control" value="{{ $hearing->client_name }}" readonly>
                    </div>
                    <div class="col-md-6 col-12">
                        <label>担当者氏名：</label>
                        <input type="text" class="form-control" value="{{ $hearing->staff_name }}" readonly>
                    </div>
                </div>

                <div class="mb-2">
                    <label>調査目的：</label>
                    <input type="text" class="form-control" value="{{ $hearing->purpose }}" readonly>
                </div>
            </div>
        </div>

        {{-- 事前情報 --}}
        <h5 class="mt-4">事前情報</h5>
        <div id="preinfo-section" class="mb-2">
            @foreach ($hearing->preinfos as $preinfo)
                <div class="row mb-2 g-2 align-items-center">
                    <input type="hidden" name="preinfo_ids[]" value="{{ $preinfo->id }}">
                    <div class="col-md-6 col-12">
                        <input type="text" name="preinfo_labels[]" class="form-control" value="{{ $preinfo->label }}" readonly>
                    </div>
                    <div class="col-md-4 col-12">
                        <input type="text" name="preinfo_values[]" class="form-control" value="{{ $preinfo->value }}" readonly>
                    </div>
                    <div class="col-md-2 col-12">
                        @if ($preinfo->image_path)
                            <img src="{{ asset('storage/' . $preinfo->image_path) }}" class="img-thumbnail w-100" style="max-height: 80px;">
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-outline-secondary mt-2 mb-3 w-100 w-md-auto" onclick="addPreinfoItem()">項目を追加</button>

        {{-- 調査項目 --}}
        <h5 class="mt-4">調査項目</h5>
        <div id="investigation-items-section" class="mb-3">
            @foreach ($hearing->items as $item)
                <div class="mb-2">
                    <input type="text" class="form-control" name="existing_investigation_items[]" value="{{ $item->item_label }}" readonly>
                </div>
            @endforeach
        </div>

        <div class="mb-4">
            <div id="new-investigation-items"></div>
            <button type="button" class="btn btn-outline-secondary mt-2 w-100 w-md-auto" onclick="addInvestigationItem()">調査項目の追加</button>
        </div>

        {{-- 所感＋社名 --}}
        <div class="card shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-md-4">

                {{-- 社名（プルダウン） --}}
                <div class="row g-3 align-items-end mb-3">
                <div class="col-md-6 col-12">
                    <label for="company_name" class="form-label mb-1">社名：</label>
                    @php
                    $companySelected = old('company_name', $report->company_name ?? '');
                    @endphp
                    <select id="company_name" name="company_name" class="form-select" required>
                    <option value="" {{ $companySelected === '' ? 'selected' : '' }}>選択してください</option>
                    <option value="探偵法人調査司会" {{ $companySelected === '探偵法人調査司会' ? 'selected' : '' }}>探偵法人調査司会</option>
                    <option value="探偵興信所一般社団法人" {{ $companySelected === '探偵興信所一般社団法人' ? 'selected' : '' }}>探偵興信所一般社団法人</option>
                    <option value="トラブル相談センター" {{ $companySelected === 'トラブル相談センター' ? 'selected' : '' }}>トラブル相談センター</option>
                    </select>
                </div>
                </div>

                {{-- 担当者所感 --}}
                <div>
                <label for="staff_comment" class="form-label mb-1">担当者所感：</label>
                <textarea id="staff_comment"
                            name="staff_comment"
                            class="form-control"
                            rows="4"
                            placeholder="担当者としての所感を記入してください">{{ old('staff_comment', $report->staff_comment ?? '') }}</textarea>
                </div>

            </div>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">調査内容</h4>

        {{-- 調査内容 本体 --}}
        <div id="content-section">
            @if (!empty($report) && $report->contents->count())
                @foreach ($report->contents as $i => $content)
                    <div class="outer-content border p-3 mb-4">
                        <div class="mb-3">
                            <label>調査内容入力</label>
                            <input type="text" name="content_summary[]" class="form-control" value="{{ $content->summary }}">
                            <input type="hidden" name="content_ids[]" value="{{ $content->id ?? '' }}">
                        </div>

                        <div class="result-items mb-3">
                            @foreach ($content->results as $j => $result)
                                <div class="inner-content border p-3 mb-3">
                                    <div class="mb-2">
                                        <label>調査日</label>
                                        <input type="date" name="content_dates[{{ $i }}][]" class="form-control" value="{{ $result->date }}">
                                    </div>
                                    <div class="mb-2">
                                        <label>調査結果入力（時間・住所（URL可））</label>
                                        <textarea name="content_descriptions[{{ $i }}][]" class="form-control">{{ $result->description }}</textarea>
                                        <input type="hidden" name="result_ids[{{ $i }}][]" value="{{ $result->id ?? '' }}">
                                    </div>
                                    <div class="mb-2">
                                        <label>画像</label>
                                        <input type="file" name="content_images[{{ $i }}][{{ $j }}][]" multiple onchange="previewImages(this)">
                                        <div class="preview-area mt-2">
                                            @if (!empty($result->image_paths))
                                                @foreach ($result->image_paths as $img)
                                                    <img src="{{ asset('storage/' . $img) }}" class="img-thumbnail me-2" style="max-height: 100px;">
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" class="btn btn-outline-secondary btn-sm mb-3 w-100 w-md-auto" onclick="addResultItem(this)">調査結果を追加</button>
                    </div>
                @endforeach
            @else
                <div class="outer-content border p-3 mb-4">
                    <div class="mb-2">
                        <label>調査日</label>
                        <input type="date" name="content_dates[0][]" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>調査内容入力</label>
                        <input type="text" name="content_summary[]" class="form-control" placeholder="調査内容入力">
                    </div>
                    <div class="result-items mb-3">
                        <div class="inner-content border p-3 mb-3">
                            <div class="mb-2">
                                <label>調査結果入力（時間・住所（URL可））</label>
                                <textarea name="content_descriptions[0][]" class="form-control" placeholder="調査結果を入力してください"></textarea>
                            </div>
                            <div class="mb-2">
                                <label>画像</label>
                                <input type="file" name="content_images[0][0][]" multiple onchange="previewImages(this)">
                                <div class="preview-area mt-2"></div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mb-3 w-100 w-md-auto" onclick="addResultItem(this)">調査結果を追加</button>
                </div>
            @endif
        </div>

        <button type="button" class="btn btn-outline-secondary w-100 w-md-auto" onclick="addContentItem()">調査内容の追加</button>

        {{-- 動画 --}}
        <hr class="my-4">
        <h4 class="mb-3">関連動画の登録</h4>

        <div class="mb-4">
            <label for="videos" class="mb-1">動画ファイル（複数選択可）</label>
            <input type="file" name="videos[]" accept="video/*" class="form-control" multiple>

            @if (!empty($report?->videos))
                <div class="mt-3">
                    @foreach ($report->videos as $index => $video)
                        <div class="video-block mb-3">
                            <div class="video-title">動画{{ $index + 1 }}</div>
                            <video controls playsinline preload="metadata">
                                {{-- iOS/Androidの互換性を少しでも上げるため type は mp4 を先頭に --}}
                                <source src="{{ asset('storage/' . $video->video_path) }}" type="video/mp4">
                                {{-- 必要に応じて他形式を追加
                                <source src="{{ asset('storage/' . $video->video_path_webm) }}" type="video/webm">
                                <source src="{{ asset('storage/' . $video->video_path_mov) }}" type="video/quicktime">
                                --}}
                                ブラウザが video タグをサポートしていません。
                            </video>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ▼ フッターアクション --}}
        <div class="mt-4">
            {{-- PC/タブレット（横並び・左に戻る、右にプレビュー＆保存） --}}
            <div class="d-none d-md-flex justify-content-between align-items-center">
                <a href="{{ route('hearingsheet.list') }}" class="btn btn-outline-secondary">
                一覧に戻る
                </a>
                <div class="d-flex gap-2">
                @if(isset($report))
                    <a href="{{ route('report.preview', $report->id) }}" class="btn btn-outline-secondary btn-lg px-4">
                    プレビュー
                    </a>
                @endif
                <button type="submit" class="btn btn-success btn-lg px-4">保存</button>
                </div>
            </div>

            {{-- モバイル（戻るは1段目フル幅／プレビュー・保存は2段目で横並び等幅） --}}
            <div class="d-md-none d-flex flex-column gap-2">
                <a href="{{ route('hearingsheet.list') }}" class="btn btn-outline-secondary w-100">
                一覧に戻る
                </a>
                <div class="d-flex flex-row gap-2 btn-row-mobile">
                @if(isset($report))
                    <a href="{{ route('report.preview', $report->id) }}"
                    class="btn btn-outline-secondary btn-lg px-3 flex-fill text-center">
                    プレビュー
                    </a>
                @endif
                <button type="submit" class="btn btn-success btn-lg px-3 flex-fill">保存</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
let contentIndex = {{ isset($report) ? $report->contents->count() : 1 }};
function addPreinfoItem() {
    const div = document.createElement('div');
    div.classList.add('row', 'mb-2', 'g-2', 'align-items-center');
    div.innerHTML = `
        <div class="col-md-6 col-12">
            <input type="text" name="preinfo_labels[]" class="form-control" placeholder="項目名">
        </div>
        <div class="col-md-4 col-12">
            <input type="text" name="preinfo_values[]" class="form-control" placeholder="内容">
        </div>
        <div class="col-md-2 col-12">
            <input type="file" name="preinfo_images[]" class="form-control">
        </div>`;
    document.getElementById('preinfo-section').appendChild(div);
}

function addResultItem(button) {
    const outer = button.closest('.outer-content');
    const items = outer.querySelector('.result-items');
    const idx = Array.from(document.querySelectorAll('.outer-content')).indexOf(outer);

    const innerHTML = `
    <div class="inner-content border p-3 mb-3">
        <div class="mb-2">
            <label>調査日</label>
            <input type="date" name="content_dates[${idx}][]" class="form-control">
        </div>
        <div class="mb-2">
            <label>調査結果入力（時間・住所（URL可））</label>
            <textarea name="content_descriptions[${idx}][]" class="form-control" placeholder="調査結果を入力してください"></textarea>
        </div>
        <div class="mb-2">
            <label>画像</label>
            <input type="file" name="content_images[${idx}][][]" multiple onchange="previewImages(this)">
            <div class="preview-area mt-2"></div>
        </div>
    </div>`;
    items.insertAdjacentHTML('beforeend', innerHTML);
}

function addContentItem() {
    const outerHTML = `
    <div class="outer-content border p-3 mb-4">
        <div class="mb-3">
            <label>調査内容入力</label>
            <input type="text" name="content_summary[]" class="form-control" placeholder="調査内容入力">
        </div>
        <div class="result-items mb-3">
            <div class="inner-content border p-3 mb-3">
                <div class="mb-2">
                    <label>調査日</label>
                    <input type="date" name="content_dates[${contentIndex}][]" class="form-control">
                </div>
                <div class="mb-2">
                    <label>調査結果入力（時間・住所（URL可））</label>
                    <textarea name="content_descriptions[${contentIndex}][]" class="form-control" placeholder="調査結果を入力してください"></textarea>
                </div>
                <div class="mb-2">
                    <label>画像</label>
                    <input type="file" name="content_images[${contentIndex}][][]" multiple onchange="previewImages(this)">
                    <div class="preview-area mt-2"></div>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm mb-3 w-100 w-md-auto" onclick="addResultItem(this)">調査結果を追加</button>
    </div>`;
    document.getElementById('content-section').insertAdjacentHTML('beforeend', outerHTML);
    contentIndex++;
}

function addInvestigationItem() {
    const section = document.getElementById('new-investigation-items');
    const div = document.createElement('div');
    div.classList.add('mb-2');
    div.innerHTML = `<input type="text" name="new_investigation_items[]" class="form-control" placeholder="新しい調査項目">`;
    section.appendChild(div);
}

function previewImages(input) {
    const previewArea = input.nextElementSibling;
    previewArea.innerHTML = '';
    Array.from(input.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.classList.add('img-thumbnail', 'me-2', 'mb-2');
            img.style.maxHeight = '100px';
            previewArea.appendChild(img);
        }
        reader.readAsDataURL(file);
    });
}
</script>
@endsection
