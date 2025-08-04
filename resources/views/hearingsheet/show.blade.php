@extends('app')

@section('title', 'ヒアリングシート詳細')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">ヒアリングシート詳細（ID: {{ $sheet->id }}）</h2>

    <div class="mb-3">
        <strong>日時：</strong> {{ $sheet->interview_datetime }}<br>
        <strong>調査種類：</strong> {{ $sheet->investigation_type }}<br>
        <strong>依頼人氏名：</strong> {{ $sheet->client_name }}<br>
        <strong>担当者氏名：</strong> {{ $sheet->staff_name }}<br>
        <strong>調査目的：</strong> {{ $sheet->purpose }}
    </div>

    <h5 class="mt-4">事前情報</h5>
    <ul class="list-group mb-4">
        @foreach($sheet->preinfos as $preinfo)
            <li class="list-group-item">
                <strong>{{ $preinfo->label }}：</strong> {{ $preinfo->value }}
                @if($preinfo->image_path)
                    <div>
                        <img src="{{ asset('storage/' . $preinfo->image_path) }}" alt="画像" style="max-width: 200px;">
                    </div>
                @endif
            </li>
        @endforeach
    </ul>

    <h5 class="mt-4">調査項目</h5>
    <ul class="list-group">
        @foreach($sheet->items as $item)
            <li class="list-group-item">{{ $item->item_label }}</li>
        @endforeach
    </ul>

    <div class="mt-4">
        <a href="{{ route('hearingsheet.list') }}" class="btn btn-secondary">一覧に戻る</a>
    </div>
</div>
@endsection
