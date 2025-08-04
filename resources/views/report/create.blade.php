@extends('app')

@section('title', '報告書作成')

@section('style')
<style>
@media (max-width: 768px) {
    body, input, textarea, label, button, .container {
        font-size: 2.2rem !important;
    }
    h2, h4, h5 {
        font-size: 2.4rem !important;
    }
    .btn {
        padding: 1.5rem 3rem !important;
    }
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    .text-end {
        text-align: center !important;
    }
    .preview-area img {
        max-width: 100%;
        height: auto;
    }
    .form-control {
        margin-bottom: 1.5rem;
    }
}
</style>
@endsection

@section('content')
<div class="container mt-5 pb-5">
    <h2 class="mb-4 text-center">報告書入力</h2>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $message)
                <div>{{ $message }}</div>
            @endforeach
        </div>
    @endif
    <form method="POST" action="{{ route('report.store') }}" enctype="multipart/form-data">
        @csrf

        <input type="hidden" name="hearing_sheet_id" value="{{ $hearing->id }}">

        <div class="row mb-3">
            <div class="col-md-6 col-12 mb-3">
                <label>日時：</label>
                <input type="datetime-local" name="report_datetime" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}">
            </div>
            <div class="col-md-6 col-12">
                <label>調査種類：</label>
                <input type="text" class="form-control" value="{{ $hearing->investigation_type }}" readonly>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6 col-12 mb-3">
                <label>依頼人氏名：</label>
                <input type="text" class="form-control" value="{{ $hearing->client_name }}" readonly>
            </div>
            <div class="col-md-6 col-12">
                <label>担当者氏名：</label>
                <input type="text" class="form-control" value="{{ $hearing->staff_name }}" readonly>
            </div>
        </div>

        <div class="mb-4">
            <label>調査目的：</label>
            <input type="text" class="form-control" value="{{ $hearing->purpose }}" readonly>
        </div>

        <h5 class="mt-4">事前情報</h5>
        <div id="preinfo-section">
            @foreach ($hearing->preinfos as $preinfo)
                <div class="row mb-2">
                    <input type="hidden" name="preinfo_ids[]" value="{{ $preinfo->id }}"> {{-- ← 追加 --}}
                    <div class="col-md-6 col-12 mb-2">
                        <input type="text" name="preinfo_labels[]" class="form-control" value="{{ $preinfo->label }}" readonly>
                    </div>
                    <div class="col-md-4 col-12 mb-2">
                        <input type="text" name="preinfo_values[]" class="form-control" value="{{ $preinfo->value }}" readonly>
                    </div>
                    <div class="col-md-2 col-12">
                        @if ($preinfo->image_path)
                            <img src="{{ asset('storage/' . $preinfo->image_path) }}" class="img-thumbnail" style="max-height: 80px;">
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <button type="button" class="btn btn-outline-secondary mt-2" onclick="addPreinfoItem()">項目を追加</button>

        <h5 class="mt-4">調査項目</h5>
        <div id="investigation-items-section">
            @foreach ($hearing->items as $item)
                <div class="mb-2">
                    <input type="text" class="form-control" name="existing_investigation_items[]" value="{{ $item->item_label }}" readonly>
                </div>
            @endforeach
        </div>

        <div class="mb-3">
            <div id="new-investigation-items"></div>
            <button type="button" class="btn btn-outline-secondary mt-2" onclick="addInvestigationItem()">調査項目の追加</button>
        </div>

        <div class="mt-4">
            <label>担当者所感：</label>
            <textarea name="staff_comment" class="form-control" rows="4" placeholder="担当者としての所感を記入してください">{{ old('staff_comment', $report->staff_comment ?? '') }}</textarea>
        </div>

        <hr class="my-5">
        <h4 class="mb-3">調査内容</h4>

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
                                        <input type="date" name="content_dates[{{ $i }}][]" class="form-control"
                                            value="{{ $result->date }}">
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

                        <button type="button" class="btn btn-outline-secondary btn-sm mb-3" onclick="addResultItem(this)">調査結果を追加</button>
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
                    <button type="button" class="btn btn-outline-secondary btn-sm mb-3" onclick="addResultItem(this)">調査結果を追加</button>
                </div>
            @endif
        </div>

        <button type="button" class="btn btn-outline-secondary" onclick="addContentItem()">調査内容の追加</button>
        
        <div class="d-flex justify-content-between mt-4">
            @if(isset($report))
                <a href="{{ route('report.preview', $report->id) }}" class="btn btn-outline-secondary btn-lg px-5">
                    プレビュー
                </a>
            @else
                <div></div> {{-- 左スペース保持 --}}
            @endif

            <button type="submit" class="btn btn-success btn-lg px-5">保存</button>
        </div>
    </form>
</div>

@section('script')
<script>
let contentIndex = {{ isset($report) ? $report->contents->count() : 1 }};
function addPreinfoItem() {
    const div = document.createElement('div');
    div.classList.add('row', 'mb-2');
    div.innerHTML = `
        <div class="col-md-6">
            <input type="text" name="preinfo_labels[]" class="form-control" placeholder="項目名">
        </div>
        <div class="col-md-4">
            <input type="text" name="preinfo_values[]" class="form-control" placeholder="内容">
        </div>
        <div class="col-md-2">
            <input type="file" name="preinfo_images[]">
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
        <button type="button" class="btn btn-outline-secondary btn-sm mb-3" onclick="addResultItem(this)">調査結果を追加</button>
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
            img.classList.add('img-thumbnail', 'me-2');
            img.style.maxHeight = '100px';
            previewArea.appendChild(img);
        }
        reader.readAsDataURL(file);
    });
}
</script>
@endsection
