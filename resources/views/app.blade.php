<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', '探偵報告書システム')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* 共通トースト */
        .app-toast{
        position: fixed;
        top: calc(env(safe-area-inset-top, 0) + 8px); /* iOSノッチ考慮 */
        left: 50%;
        transform: translateX(-50%);
        width: clamp(240px, 92vw, 560px); /* 画面に収まる可変幅 */
        max-width: 100vw;
        margin: 0;
        padding: .75rem 1rem;
        box-sizing: border-box;
        z-index: 1050;
        border-radius: .75rem;
        word-break: break-word;      /* 日本語の長文も折り返し */
        overflow-wrap: anywhere;     /* URLなど英数字が長くても折り返し */
        }

        .app-toast:empty { display: none; }  /* テキストが空なら枠ごと非表示に */

        /* 極小端末向けの微調整 */
        @media (max-width: 375px){
        .app-toast{ padding: .6rem .8rem; width: 94vw; }
        }
    </style>
    @yield('style')
</head>
<body>
    @yield('content')
</body>
    @yield('script')
</html>