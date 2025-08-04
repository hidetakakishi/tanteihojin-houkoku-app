@extends('app')

@section('title', 'ヒアリングシート一覧')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">ヒアリングシート一覧</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>日時</th>
                <th>調査種類</th>
                <th>依頼人</th>
                <th>担当者</th>
                <th>調査目的</th>
                <th>詳細</th>
                <th>報告書作成</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sheets as $sheet)
                <tr>
                    <td>{{ $sheet->id }}</td>
                    <td>{{ $sheet->interview_datetime }}</td>
                    <td>{{ $sheet->investigation_type }}</td>
                    <td>{{ $sheet->client_name }}</td>
                    <td>{{ $sheet->staff_name }}</td>
                    <td>{{ Str::limit($sheet->purpose, 30) }}</td>
                    <td>
                        <a href="{{ route('hearingsheet.show', $sheet->id) }}" class="btn btn-sm btn-info">
                            詳細
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('report.create', ['id' => $sheet->id]) }}" class="btn btn-sm btn-primary">報告書作成</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $sheets->links() }}
</div>
@endsection