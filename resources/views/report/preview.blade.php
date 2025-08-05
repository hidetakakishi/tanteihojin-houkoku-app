<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', '探偵報告書システム')</title>
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: ipag;
        }
    </style>
</head>
<body>
    @php
    $isPdf = request()->routeIs('report.download_pdf');
    @endphp
    <div style="margin: 50px; font-size: 1.1rem;">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="font-size: 1.5rem;">調査報告書</div>
        </div>

        <div style="padding: 20px; margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <div>{{ now()->format('Y年m月d日') }}</div>
                <div>担当者：{{ $report->hearingSheet->staff_name }}</div>
            </div>
            <div style="margin-bottom: 5px;">ご依頼者氏名：{{ $report->hearingSheet->client_name }}</div>
            <div>調査目的：{{ $report->hearingSheet->purpose }}</div>
        </div>

        <div style="margin-bottom: 5px;">事前情報</div>
        <div style="border: 1px solid #000; padding: 15px; margin-bottom: 30px;">
            @foreach ($report->hearingSheet->preinfos as $preinfo)
                <div style="margin-bottom: 20px;">
                    <div style="margin-bottom: 5px;">{{ $preinfo->label }}</div>
                    <div style="margin-bottom: 10px;">{{ $preinfo->value }}</div>
                    @if ($preinfo->image_path)
                        <div>
                            <img src="{{ $isPdf ? public_path('storage/' . $preinfo->image_path) : asset('storage/' . $preinfo->image_path) }}" style="max-height: 300px; border: 1px solid #000;">
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div style="margin-bottom: 5px;">調査結果</div>
        <div style="border: 1px solid #000; padding: 15px; margin-bottom: 30px;">
            @foreach ($report->hearingSheet->items as $item)
                <div style="margin-bottom: 10px;">
                    {{ $item->item_label }}：入力された内容
                </div>
            @endforeach
        </div>

        <div style="margin-bottom: 5px;">担当者所見</div>
        <div style="margin-bottom: 30px;">
            {{ $report->staff_comment }}
        </div>

        @foreach ($report->contents as $content)
            <div style="margin-bottom: 5px;">{{ $content->summary }}</div>
            <div style="border: 1px solid #000; padding: 15px; margin-bottom: 30px;">
                @foreach ($content->results as $result)
                    <div style="border: 1px solid #000; padding: 10px; margin-bottom: 15px;">
                        <div style="margin-bottom: 5px;">日付</div>
                        <div style="margin-bottom: 10px;">{{ $result->description }}</div>
                        @if (is_array($result->image_paths))
                            @foreach ($result->image_paths as $img)
                                <div style="margin-bottom: 10px;">
                                    <img src="{{ $isPdf ? public_path('storage/' . $img) : asset('storage/' . $img) }}" style="max-height: 300px; border: 1px solid #000;">
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach

        @if(!$isPdf && $report->videos->count())
            <div style="margin: 50px;">
                <div style="font-size: 1.2rem; font-weight: bold; margin-bottom: 15px;">関連動画</div>

                @foreach ($report->videos as $index => $video)
                    <div style="margin-bottom: 25px;">
                        <div style="margin-bottom: 5px; font-weight: bold;">動画{{ $index + 1 }}</div>
                            <video controls width="100%" style="max-height: 300px;">
                                <source src="{{ asset('storage/' . $video->video_path) }}" type="video/mp4">
                                ブラウザが video タグをサポートしていません。
                            </video>
                    </div>
                @endforeach
            </div>
        @endif

        @if(!$isPdf)
            <div style="margin-bottom: 30px;">
                <form action="{{ route('report.download_pdf', ['id' => $report->id]) }}" method="GET">
                    <button type="submit" style="padding: 10px 20px; border: 1px solid #000; background-color: #f5f5f5; cursor: pointer;">PDFで出力</button>
                </form>
            </div>
        @endif
    </div>
</body>
</html>