@extends('app')

@section('content')
<div class="container mt-5">
    <h3>報告書閲覧</h3>
    <form method="POST" action="{{ route('report.public.view', $report->report_key) }}">
        @csrf
        <div class="mb-3">
            <label>アクセスキー</label>
            <input type="text" name="access_key" class="form-control" required>
            @error('access_key') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="btn btn-primary">表示する</button>
    </form>
</div>
@endsection