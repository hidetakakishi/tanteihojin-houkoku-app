@extends('app')

@section('title', 'ホーム')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">報告書管理システム</h2>

    <p>ようこそ、{{ Auth::user()->name }}さん。</p>

    <div class="d-flex gap-3 mt-4">
        <a href="{{ route('hearingsheet.index') }}" class="btn btn-primary">
            📋 ヒアリングシートを作成する
        </a>

        <a href="{{ route('hearingsheet.list') }}" class="btn btn-primary">
            ヒアリングシート一覧
        </a>
    </div>
</div>
@endsection