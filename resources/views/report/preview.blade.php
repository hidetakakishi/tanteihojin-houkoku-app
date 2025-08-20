<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', '探偵報告書システム')</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
    /* ==== フォント（Regular / Bold を明示） ==== */
    @font-face{
        font-family: 'NotoSansJP';
        font-style: normal;
        font-weight: 400;
        src: url("{{ storage_path('fonts/NotoSansJP-Regular.ttf') }}") format('truetype');
    }
    @font-face{
        font-family: 'NotoSansJP';
        font-style: normal;
        font-weight: 700;
        src: url("{{ storage_path('fonts/NotoSansJP-Bold.ttf') }}") format('truetype');
    }

    /* ==== ベース ==== */
    html, body { font-family: 'NotoSansJP', sans-serif; font-weight: 400; }
    * { font-weight: normal; }                 /* dompdf の一律太字化を抑止 */
    strong, b, .heading, th { font-weight: 700; }

    .doc-container { margin: 50px; font-size: 12pt; }
    .title-block { text-align: center; margin-bottom: 30px; }
    .title { font-size: 16pt; font-weight: 700; }

    .intro { padding: 20px; margin-bottom: 30px; }

    /* ==== 2カラム（dompdf 安定：inline-block） ==== */
    .row-split{
      font-size: 0;               /* インラインブロック隙間対策（子で再指定） */
      white-space: nowrap;
      width: 100%;
    }
    .row-split + .row-split { margin-top: 6px; }
    .row-split .left,
    .row-split .right{
      display: inline-block;
      vertical-align: top;
      font-size: 12pt;            /* 親の 0 を打ち消す */
    }
    /* 上段：日付（左）55% / 社名＋印鑑（右）45% */
    .row-date-company .left  { width: 55%; text-align: left; }
    .row-date-company .right { width: 45%; text-align: right; }
    /* 下段：ご依頼者 / 担当者 */
    .row-client-staff .left,
    .row-client-staff .right { width: 50%; }
    .row-client-staff .left  { text-align: left; }
    .row-client-staff .right { text-align: right; }

    .company-name{ display: inline-block; vertical-align: middle; }
    .seal-img{ height: 60px; width: auto; margin-left: 8px; vertical-align: middle; }

    .purpose { margin-top: 6px; }

    /* ==== セクション見出し & ボックス ==== */
    .heading { font-size: 13pt; margin: 18px 0 10px; }
    .section-box { border: 1px solid #000; padding: 15px; margin-bottom: 30px; }

    /* 事前情報 */
    .preinfo-item { margin-bottom: 20px; }
    .preinfo-label { margin-bottom: 5px; }
    .preinfo-value { margin-bottom: 10px; }
    .preinfo-img { max-height: 300px; border: 1px solid #000; }

    /* ==== 詳細結果テーブル ==== */
    .results-table{
      width: 100%;
      table-layout: fixed;
      border: 1px solid #000;     /* 外枠はテーブル自身に付ける */
      border-collapse: separate;  /* dompdf で安定 */
      border-spacing: 0;
      margin: 0;
    }
    .results-table td{
      vertical-align: top;        /* セル上寄せ */
      padding: 6px 8px;           /* 上からすぐ表示（上=6px） */
      border-top: none;          /* ← 行ごとの線を消す */
    }
    .col-date{ width: 15%; border-right: 1px solid #000; }
    .col-body{ width: 85%; border: none; }

    /* セル内の最初のブロックを block 化して上端を完全に揃える */
    .date-text,
    .result-desc{
      display: block;
      margin: 0;
      padding: 0;
      line-height: 1.5;
      white-space: pre-wrap;
      word-wrap: break-word;
      overflow-wrap: break-word;
      word-break: break-word;
    }

    .result-img{
      max-width: 100%;
      height: auto;
      max-height: 300px;
      margin-top: 8px;
      margin-bottom: 10px;
    }

    /* 動画（ブラウザ時のみ表示の見た目用） */
    .video-wrap { margin: 50px; }
    .video-item { margin-bottom: 25px; }
    .video-title { margin-bottom: 5px; }
    video { max-height: 300px; width:100%; height:auto; border-radius: 8px; }

    .spacer { height: 40px; }

    /* ==== ブラウザ表示時のアクションボタン ==== */
    .pdf-actions{
      margin:24px 0;
      display:flex;
      gap:.6rem;
      flex-wrap:wrap;
    }
    .pdf-btn{
      display:inline-flex; align-items:center; gap:.5rem;
      padding:.75rem 1.25rem;
      border:1px solid #cfd4da; border-radius:.5rem;
      background:#fff; color:#495057; text-decoration:none; cursor:pointer;
      transition:background-color .15s ease, box-shadow .15s ease, transform .05s ease;
      font-weight: 600; font-size: 12pt;
    }
    .pdf-btn:hover{ background:#f8f9fa; box-shadow:0 .5rem 1.25rem rgba(0,0,0,.08); }
    .pdf-btn:active{ transform: translateY(1px); }
    .pdf-btn-icon{ font-size: 13pt; line-height:1; }
    .pdf-actions .pdf-btn{ flex:0 1 auto; }
    @media (max-width: 767.98px){
      .pdf-actions .pdf-btn{ flex:1 1 0; justify-content:center; }
    }
    </style>
</head>
<body>
@php
    $isPdf = request()->routeIs('report.download_pdf');

    // 社名と印鑑画像の対応
    $company   = $report->company_name ?? '株式会社 探偵事務所';
    $sealFiles = [
        '探偵法人調査司会'       => 'tyousasikai.png',
        '探偵興信所一般社団法人' => 'syadan.png',
        'トラブル相談センター'   => 'trouble.png',
    ];
    $sealFile = $sealFiles[$company] ?? null;
    $sealSrc  = $sealFile
        ? ($isPdf ? public_path('seals/'.$sealFile) : asset('seals/'.$sealFile))
        : null;
@endphp

<div class="doc-container">
    {{-- タイトル --}}
    <div class="title-block">
        <div class="title">調査報告書</div>
    </div>

    {{-- ヘッダー情報 --}}
    <div class="intro">
        <div class="row-split row-date-company">
            <div class="left">{{ now()->format('Y年m月d日') }}</div>
            <div class="right">
                <span class="company-name">{{ $company }}</span>
                @if($sealSrc)
                    <img class="seal-img" src="{{ $sealSrc }}">
                @endif
            </div>
        </div>
        <div class="row-split row-client-staff">
            <div class="left">ご依頼者氏名：{{ $report->hearingSheet->client_name }}</div>
            <div class="right">担当者：{{ $report->hearingSheet->staff_name }}</div>
        </div>
        <div class="purpose">調査目的：{{ $report->hearingSheet->purpose }}</div>
    </div>

    {{-- 事前情報 --}}
    <div class="heading">事前情報</div>
    <div class="section-box">
        @foreach ($report->hearingSheet->preinfos as $preinfo)
            <div class="preinfo-item">
                <div class="preinfo-label">{{ $preinfo->label }}</div>
                <div class="preinfo-value">{{ $preinfo->value }}</div>
                @if ($preinfo->image_path)
                    <div>
                        <img class="preinfo-img"
                             src="{{ $isPdf ? public_path('storage/' . $preinfo->image_path) : asset('storage/' . $preinfo->image_path) }}">
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- 調査結果（チェック項目の羅列） --}}
    <div class="heading">調査結果</div>
    <div class="section-box">
        @foreach ($report->hearingSheet->items as $item)
            <div class="result-line">{{ $item->item_label }}：入力された内容</div>
        @endforeach
    </div>

    {{-- 担当者所感 --}}
    <div class="heading">担当者所感</div>
    <div class="section-box">
        {{ $report->staff_comment }}
    </div>

    {{-- 詳細コンテンツ --}}
    @foreach ($report->contents as $content)
        <div class="heading">{{ $content->summary }}</div>
        <table class="results-table">
            <tbody>
            @foreach ($content->results as $result)
                <tr>
                    <td class="col-date">
                        <div class="date-text">{{ \Carbon\Carbon::parse($result->date)->format('n月j日') }}</div>
                    </td>
                    <td class="col-body">
                        <div class="result-desc">{{ $result->description }}</div>
                        @if (is_array($result->image_paths))
                            @foreach ($result->image_paths as $img)
                                <img class="result-img"
                                     src="{{ $isPdf ? public_path('storage/' . $img) : asset('storage/' . $img) }}">
                            @endforeach
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endforeach

    {{-- ブラウザ時のみ動画を表示 --}}
    @if(!$isPdf && $report->videos->count())
        <div class="video-wrap">
            <div class="heading">関連動画</div>
            @foreach ($report->videos as $index => $video)
                <div class="video-item">
                    <div class="video-title">動画{{ $index + 1 }}</div>
                    <video controls>
                        <source src="{{ asset('storage/' . $video->video_path) }}" type="video/mp4">
                        ブラウザが video タグをサポートしていません。
                    </video>
                </div>
            @endforeach
        </div>
    @endif

    <div class="spacer"></div>

    {{-- ブラウザ時のみ：戻る & PDF出力ボタン --}}
    @if(!$isPdf)
        <div class="pdf-actions">
            <a href="{{ route('report.create', ['id' => $report->hearing_sheet_id]) }}"
               class="pdf-btn" title="報告書作成に戻る">
                <span class="pdf-btn-icon">↩</span>
                報告書作成に戻る
            </a>
            <form action="{{ route('report.download_pdf', ['id' => $report->id]) }}" method="GET">
                <button type="submit" class="pdf-btn">
                    <span class="pdf-btn-icon">🧾</span>
                    PDFで出力
                </button>
            </form>
        </div>
    @endif
</div>
</body>
</html>
