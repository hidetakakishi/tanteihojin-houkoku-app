@extends('app')

@section('title', 'ヒアリングシート')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4 text-center">ヒアリングシート</h2>

    <form method="POST" action="{{ route('hearingsheet.submit') }}" enctype="multipart/form-data">
        @csrf

        <div class="row mb-3">
            <div class="col-md-6">
                <label>日時：</label>
                <input type="datetime-local" name="interview_datetime" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label>調査種類：</label>
                <select name="investigation_type" id="investigation_type" class="form-select" onchange="renderPreInfoFields(); renderSurveyItems()" required>
                    <option value="">選択してください</option>
                    <option value="行動調査">行動調査</option>
                    <option value="人探し">人探し</option>
                    <option value="浮気調査">浮気調査</option>
                    <option value="嫌がらせ調査">嫌がらせ調査</option>
                    <option value="信用調査（個人）">信用調査（個人）</option>
                    <option value="信用調査（法人・団体）">信用調査（法人・団体）</option>
                    <option value="オンライントラブル調査">オンライントラブル調査</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>依頼人氏名：</label>
                <input type="text" name="client_name" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label>担当者氏名：</label>
                <input type="text" name="staff_name" class="form-control" value="{{ Auth::user()->name }}">
            </div>
        </div>

        <div class="mb-3">
            <label>調査目的：</label>
            <input type="text" name="purpose" class="form-control" required>
        </div>

        <h5 class="mt-4">事前情報</h5>
        <div id="preinfo-area"></div>

        <h5 class="mt-4">調査項目</h5>
        <div id="survey-area"></div>

        <div class="text-center">
            <button type="submit" class="btn btn-primary mt-4">確定</button>
        </div>
    </form>
</div>

<script>
    const preinfoTemplates = {
        "行動調査": [
            "調査対象者の氏名", "生年月日または年齢", "住所またはおおよその居住地", "勤務先または通学先",
            "交友関係や家族の情報", "よく行く場所（趣味の施設や飲食店）", "SNSアカウントなどのデジタル情報",
            "車両情報（車種・色・ナンバーなど）", "過去の怪しい行動や状況の詳細", "対象の写真や画像データ"
        ],
        "人探し": [
            "調査対象者の氏名", "生年月日または年齢", "住所またはおおよその居住地", "最後にあった時期や場所",
            "勤務先・通学先・所属していた団体など", "交友関係や家族構成", "趣味・嗜好・よく行っていた場所",
            "SNSアカウントなどのデジタル情報", "過去の住所・住んでいた地域", "学歴・職歴・資格などの経歴情報",
            "金銭トラブルの有無・その金額", "警察等への相談歴", "対象の写真や画像データ"
        ],
        "浮気調査": [
            "調査対象者（配偶者・恋人）の氏名", "生年月日または年齢", "住所またはおおよその居住地",
            "勤務先または通学先", "普段の行動パターン（通勤手段、帰宅時間など）",
            "浮気が疑われる状況・日時・記録", "SNSアカウントなどのデジタル情報", "浮気相手の心当たり",
            "浮気をする場所の心当たり", "車両情報（ナンバー・車種・色など）", "弁護士等への相談歴",
            "対象、浮気相手の写真や画像データ"
        ],
        "嫌がらせ調査": [
            "嫌がらせの内容", "発生日時・頻度・期間", "嫌がらせの場所",
            "嫌がらせの起こる場所の情報（家や職場の間取りなど）", "加害者の心当たり",
            "嫌がらせのきっかけの心当たり", "同様の被害にあっている人の有無", "被害の証拠の有無",
            "警察や自治体等への相談歴", "盗聴等の心当たり", "被害実態の画像記録"
        ],
        "信用調査（個人）": [
            "調査対象者の氏名", "生年月日または年齢", "現住所またはおおよその居住地",
            "勤務先・役職・年収など", "学歴・職歴・資格などの経歴情報", "交友関係・家族構成",
            "家族の住所またはおおよその居住地", "SNSアカウントなどのデジタル情報",
            "過去の住所またはおおよその居住地", "過去のトラブル歴", "対象、浮気相手の写真や画像データ"
        ],
        "信用調査（法人・団体）": [
            "調査対象の名称", "所在地", "事業内容・活動内容", "従業員の名前",
            "SNSアカウントなどのデジタル情報", "取引内容", "他の取引先", "トラブル等の評判",
            "信用調査を行う目的", "画像データ"
        ],
        "オンライントラブル調査": [
            "トラブルの具体的な内容", "発生したSNSやサイトのURL", "トラブルが発生した日時・期間",
            "加害者と思われるアカウント", "関与しているとみられる他のアカウント", "被害者のSNSアカウント",
            "第三者とのやり取り（DM・コメント）の有無", "トラブルのきっかけの心当たり",
            "実生活への影響", "警察やプラットフォームへの相談記録", "トラブル被害のスクリーンショット"
        ]
    };

    const surveyItems = {
        "行動調査": [
            "調査対象者の追跡・行動の記録", "生活状況の確認", "職務態度の調査", "素行調査", "生活リスクの確認"
        ],
        "人探し": [
            "聞き回り", "知っている住所の確認", "すでに知っている職場等の確認", "現住所の特定", "職場の特定",
            "SNSアカウントの特定", "対象者との接触の仲介", "手紙の受け渡し"
        ],
        "嫌がらせ調査": [
            "嫌がらせの証拠収集", "嫌がらせ加害者の特定", "盗聴器の特定", "盗撮カメラの特定", "騒音被害の測定",
            "電話被害測定調査", "振動調査", "オンライン上の情報収集"
        ],
        "浮気調査": [
            "調査対象者の追跡・行動の記録", "浮気相手の特定", "浮気相手の住所特定", "浮気相手の職場特定",
            "浮気相手の身辺調査", "オンライン上の情報収集"
        ],
        "信用調査（個人）": [
            "調査対象者の追跡・行動の記録", "すでに知っている住所の確認", "すでに知っている職場等の確認",
            "経済状況の調査", "住居状況の調査", "職歴の確認", "学歴の確認", "資格の確認", "SNS等の運用調査"
        ],
        "信用調査（法人・団体）": [
            "業務・活動実態の確認", "従業員の職務態度の確認", "過去のトラブルの調査", "経営状況の調査",
            "実績の調査", "オンライン上の情報収集"
        ],
        "オンライントラブル調査": [
            "個人情報の拡散状況の確認", "誹謗中傷の証拠収集", "ネットストーカーの実態収集",
            "オンライン詐欺の証拠収集", "トラブル加害者のデジタル情報収集", "発信者情報開示請求のための証拠収集",
            "仮想通貨のトランザクション追跡調査"
        ]
    };

    function renderPreInfoFields() {
        const type = document.getElementById('investigation_type').value;
        const area = document.getElementById('preinfo-area');
        area.innerHTML = '';

        if (!preinfoTemplates[type]) return;

        preinfoTemplates[type].forEach((label, idx) => {
            const html = `
                <div class="row mb-2">
                    <div class="col-md-8">
                        <label>${label}</label>
                        <input type="text" name="preinfo[${idx}][text]" class="form-control">
                        <input type="hidden" name="preinfo[${idx}][label]" value="${label}">
                    </div>
                    <div class="col-md-4">
                        <label>画像</label>
                        <input type="file" name="preinfo[${idx}][image]" class="form-control">
                    </div>
                </div>
            `;
            area.insertAdjacentHTML('beforeend', html);
        });
    }

    function renderSurveyItems() {
        const type = document.getElementById('investigation_type').value;
        const area = document.getElementById('survey-area');
        area.innerHTML = '';

        if (!surveyItems[type]) return;

        surveyItems[type].forEach((label, i) => {
            const html = `
                <div class="form-check mb-2">
                    <input type="checkbox" name="survey_items[]" value="${label}" class="form-check-input" id="survey_item_${i}">
                    <label class="form-check-label" for="survey_item_${i}">${label}</label>
                </div>
            `;
            area.insertAdjacentHTML('beforeend', html);
        });
    }
</script>
@endsection