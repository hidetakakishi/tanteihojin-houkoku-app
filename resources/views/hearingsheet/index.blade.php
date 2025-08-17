@extends('app')

@section('title', 'ヒアリングシート')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4 text-center">ヒアリングシート</h2>

    {{-- 上部アナウンス（保存しました） --}}
    @if(session('success'))
    <div id="save-toast" class="alert app-toast alert-success" role="alert" aria-live="polite">
        {{ session('success') }}
    </div>
    @endif

    <div class="card shadow-sm rounded-4">
        <div class="card-body p-3 p-md-4">
            <form method="POST" action="{{ route('hearingsheet.submit') }}" enctype="multipart/form-data" novalidate>
                @csrf

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">日時：</label>
                        <input type="datetime-local" name="interview_datetime" class="form-control" required value="{{ old('interview_datetime') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">調査種類：</label>
                        <select name="investigation_type" id="investigation_type" class="form-select" onchange="renderPreInfoFields(); renderSurveyItems()" required>
                            <option value="">選択してください</option>
                            @foreach(["行動調査","人探し","浮気調査","嫌がらせ調査","信用調査（個人）","信用調査（法人・団体）","オンライントラブル調査"] as $opt)
                                <option value="{{ $opt }}" {{ old('investigation_type')===$opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">依頼人氏名：</label>
                        <input type="text" name="client_name" class="form-control" required value="{{ old('client_name') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">担当者氏名：</label>
                        <input type="text" name="staff_name" class="form-control" value="{{ old('staff_name', Auth::user()->name) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">調査目的：</label>
                    <input type="text" name="purpose" class="form-control" required value="{{ old('purpose') }}">
                </div>

                <h5 class="mt-4">事前情報</h5>
                <div id="preinfo-area"></div>

                <h5 class="mt-4">調査項目</h5>
                <div id="survey-area"></div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg mt-4 px-4 w-100 w-md-auto">保存</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ▼ 保存後サマリー（保存内容の表示） --}}
    @if(session('just_saved'))
        <div class="card shadow-sm rounded-4 mt-4">
            <div class="card-header bg-light fw-bold">保存内容</div>
            <div class="card-body p-3 p-md-4">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="mb-2"><span class="text-muted small">日時：</span><div class="fw-semibold">{{ old('interview_datetime') }}</div></div>
                        <div class="mb-2"><span class="text-muted small">調査種類：</span><div class="fw-semibold">{{ old('investigation_type') }}</div></div>
                        <div class="mb-2"><span class="text-muted small">依頼人：</span><div class="fw-semibold">{{ old('client_name') }}</div></div>
                        <div class="mb-2"><span class="text-muted small">担当者：</span><div class="fw-semibold">{{ old('staff_name', Auth::user()->name) }}</div></div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="mb-2"><span class="text-muted small">調査目的：</span>
                            <div class="fw-semibold" style="word-break:break-word;">{{ old('purpose') }}</div>
                        </div>
                    </div>
                </div>

                {{-- 事前情報（テキストのみプレビュー） --}}
                @php $preinfo = old('preinfo', []); @endphp
                @if(!empty($preinfo))
                    <hr class="my-3">
                    <h6 class="mb-2">事前情報</h6>
                    <div class="row g-2">
                        @foreach($preinfo as $pi)
                            @php
                                $label = $pi['label'] ?? '';
                                $text  = $pi['text'] ?? '';
                            @endphp
                            @if(trim($label) !== '' || trim($text) !== '')
                                <div class="col-12 col-md-6">
                                    <div class="border rounded p-2 h-100">
                                        <div class="text-muted small mb-1">{{ $label }}</div>
                                        <div class="fw-semibold">{{ $text }}</div>
                                        {{-- 画像は old() に含まれないためプレビュー割愛 --}}
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                {{-- 調査項目 --}}
                @php $items = old('survey_items', []); @endphp
                @if(!empty($items))
                    <hr class="my-3">
                    <h6 class="mb-2">調査項目</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($items as $it)
                            <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill px-3 py-2">{{ $it }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

{{-- スクリプト群（元のまま＋保存アナウンスの自動フェードアウト） --}}
<script>
    const OLD = {
        investigation_type: @json(old('investigation_type')),
        preinfo: @json(old('preinfo', [])),
        survey_items: @json(old('survey_items', [])),
    };
    // 既存テンプレート & レンダリング関数（省略せずそのまま）
    const preinfoTemplates = {
        "行動調査": [
            "調査対象者の氏名","生年月日または年齢","住所またはおおよその居住地","勤務先または通学先",
            "交友関係や家族の情報","よく行く場所（趣味の施設や飲食店）","SNSアカウントなどのデジタル情報",
            "車両情報（車種・色・ナンバーなど）","過去の怪しい行動や状況の詳細","対象の写真や画像データ"
        ],
        "人探し": [
            "調査対象者の氏名","生年月日または年齢","住所またはおおよその居住地","最後にあった時期や場所",
            "勤務先・通学先・所属していた団体など","交友関係や家族構成","趣味・嗜好・よく行っていた場所",
            "SNSアカウントなどのデジタル情報","過去の住所・住んでいた地域","学歴・職歴・資格などの経歴情報",
            "金銭トラブルの有無・その金額","警察等への相談歴","対象の写真や画像データ"
        ],
        "浮気調査": [
            "調査対象者（配偶者・恋人）の氏名","生年月日または年齢","住所またはおおよその居住地",
            "勤務先または通学先","普段の行動パターン（通勤手段、帰宅時間など）",
            "浮気が疑われる状況・日時・記録","SNSアカウントなどのデジタル情報","浮気相手の心当たり",
            "浮気をする場所の心当たり","車両情報（ナンバー・車種・色など）","弁護士等への相談歴",
            "対象、浮気相手の写真や画像データ"
        ],
        "嫌がらせ調査": [
            "嫌がらせの内容","発生日時・頻度・期間","嫌がらせの場所",
            "嫌がらせの起こる場所の情報（家や職場の間取りなど）","加害者の心当たり",
            "嫌がらせのきっかけの心当たり","同様の被害にあっている人の有無","被害の証拠の有無",
            "警察や自治体等への相談歴","盗聴等の心当たり","被害実態の画像記録"
        ],
        "信用調査（個人）": [
            "調査対象者の氏名","生年月日または年齢","現住所またはおおよその居住地",
            "勤務先・役職・年収など","学歴・職歴・資格などの経歴情報","交友関係・家族構成",
            "家族の住所またはおおよその居住地","SNSアカウントなどのデジタル情報",
            "過去の住所またはおおよその居住地","過去のトラブル歴","対象、浮気相手の写真や画像データ"
        ],
        "信用調査（法人・団体）": [
            "調査対象の名称","所在地","事業内容・活動内容","従業員の名前",
            "SNSアカウントなどのデジタル情報","取引内容","他の取引先","トラブル等の評判",
            "信用調査を行う目的","画像データ"
        ],
        "オンライントラブル調査": [
            "トラブルの具体的な内容","発生したSNSやサイトのURL","トラブルが発生した日時・期間",
            "加害者と思われるアカウント","関与しているとみられる他のアカウント","被害者のSNSアカウント",
            "第三者とのやり取り（DM・コメント）の有無","トラブルのきっかけの心当たり",
            "実生活への影響","警察やプラットフォームへの相談記録","トラブル被害のスクリーンショット"
        ]
    };

    const surveyItems = {
        "行動調査": ["調査対象者の追跡・行動の記録","生活状況の確認","職務態度の調査","素行調査","生活リスクの確認"],
        "人探し": ["聞き回り","知っている住所の確認","すでに知っている職場等の確認","現住所の特定","職場の特定","SNSアカウントの特定","対象者との接触の仲介","手紙の受け渡し"],
        "嫌がらせ調査": ["嫌がらせの証拠収集","嫌がらせ加害者の特定","盗聴器の特定","盗撮カメラの特定","騒音被害の測定","電話被害測定調査","振動調査","オンライン上の情報収集"],
        "浮気調査": ["調査対象者の追跡・行動の記録","浮気相手の特定","浮気相手の住所特定","浮気相手の職場特定","浮気相手の身辺調査","オンライン上の情報収集"],
        "信用調査（個人）": ["調査対象者の追跡・行動の記録","すでに知っている住所の確認","すでに知っている職場等の確認","経済状況の調査","住居状況の調査","職歴の確認","学歴の確認","資格の確認","SNS等の運用調査"],
        "信用調査（法人・団体）": ["業務・活動実態の確認","従業員の職務態度の確認","過去のトラブルの調査","経営状況の調査","実績の調査","オンライン上の情報収集"],
        "オンライントラブル調査": ["個人情報の拡散状況の確認","誹謗中傷の証拠収集","ネットストーカーの実態収集","オンライン詐欺の証拠収集","トラブル加害者のデジタル情報収集","発信者情報開示請求のための証拠収集","仮想通貨のトランザクション追跡調査"]
    };

    function renderPreInfoFields() {
        const type = document.getElementById('investigation_type').value;
        const area = document.getElementById('preinfo-area');
        area.innerHTML = '';
        if (!preinfoTemplates[type]) return;

        preinfoTemplates[type].forEach((label, idx) => {
            const val = (OLD.preinfo[idx] && OLD.preinfo[idx].text) ? OLD.preinfo[idx].text : '';
            const html = `
            <div class="row mb-2 g-2">
                <div class="col-md-8">
                <label class="form-label">${label}</label>
                <input type="text" name="preinfo[${idx}][text]" class="form-control" value="${val}">
                <input type="hidden" name="preinfo[${idx}][label]" value="${label}">
                </div>
                <div class="col-md-4">
                <label class="form-label">画像</label>
                <input type="file" name="preinfo[${idx}][image]" class="form-control">
                </div>
            </div>`;
            area.insertAdjacentHTML('beforeend', html);
        });
    }

    function renderSurveyItems() {
        const type = document.getElementById('investigation_type').value;
        const area = document.getElementById('survey-area');
        area.innerHTML = '';
        if (!surveyItems[type]) return;

        surveyItems[type].forEach((label, i) => {
            const id = `survey_item_${i}`;
            const checked = Array.isArray(OLD.survey_items) && OLD.survey_items.includes(label) ? 'checked' : '';
            const html = `
            <div class="form-check mb-2">
                <input type="checkbox" name="survey_items[]" value="${label}" class="form-check-input" id="${id}" ${checked}>
                <label class="form-check-label" for="${id}">${label}</label>
            </div>`;
            area.insertAdjacentHTML('beforeend', html);
        });
    }

    // 初期表示（old値がある場合に再描画）
    document.addEventListener('DOMContentLoaded', () => {
        if (OLD.investigation_type) {
            document.getElementById('investigation_type').value = OLD.investigation_type;
            renderPreInfoFields();
            renderSurveyItems();
        }
        const toast = document.getElementById('save-toast');
        if (toast) {
            setTimeout(() => {
            toast.classList.add('is-hidden');
            setTimeout(() => toast.remove(), 400);
            }, 1800);
        }
    });
</script>

{{-- モバイル見やすさ向上CSS --}}
<style>
@media (max-width: 767.98px) {
    .card .form-label { font-size: .98rem; }
    .card .btn { padding: .8rem 1rem; }
    .btn-lg { font-size: 1.05rem; }
}
.bg-secondary-subtle { background-color: rgba(108,117,125,.12)!important; }
.text-secondary-emphasis { color: #495057!important; }
</style>
@endsection
