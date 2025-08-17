@extends('app')
@section('title', '管理者 - ユーザー一覧')

@section('content')
<div class="container mt-5">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <h2 class="mb-0">ユーザー管理</h2>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">＋ ユーザー作成</a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
  @endif

  <form method="GET" class="mb-3">
    <div class="input-group">
      <input type="text" name="q" class="form-control" placeholder="名前 / メールで検索" value="{{ $q }}">
      <button class="btn btn-outline-secondary">検索</button>
    </div>
  </form>

  <div class="table-responsive d-none d-md-block">
    <table class="table align-middle modern-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>名前</th>
          <th>メール</th>
          <th>権限</th>
          <th class="text-end" style="width:220px;">操作</th>
        </tr>
      </thead>
      <tbody>
        @foreach($users as $u)
          <tr>
            <td>#{{ $u->id }}</td>
            <td>{{ $u->name }}</td>
            <td>{{ $u->email }}</td>
            <td>
              @if($u->is_admin)
                <span class="badge bg-dark">管理者</span>
              @else
                <span class="badge bg-secondary">一般</span>
              @endif
            </td>
            <td class="text-end">
              <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-primary">編集</a>
              <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('ユーザー「{{ $u->name }}」を削除します。よろしいですか？');">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">削除</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- モバイル用カード --}}
  <div class="d-md-none d-flex flex-column gap-3">
    @foreach($users as $u)
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="fw-bold">#{{ $u->id }} {{ $u->name }}</div>
            <div>
              @if($u->is_admin)
                <span class="badge bg-dark">管理者</span>
              @else
                <span class="badge bg-secondary">一般</span>
              @endif
            </div>
          </div>
          <div class="text-muted small">{{ $u->email }}</div>
          <div class="d-grid gap-2 mt-2">
            <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-outline-primary btn-sm">編集</a>
            <form action="{{ route('admin.users.destroy', $u) }}" method="POST"
                  onsubmit="return confirm('ユーザー「{{ $u->name }}」を削除します。よろしいですか？');">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn-outline-danger btn-sm">削除</button>
            </form>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-3">{{ $users->links() }}</div>
</div>

<style>
.modern-table thead th{
  background: #fff; position: sticky; top:0; z-index:1;
  border-bottom: 1px solid rgba(0,0,0,.08);
}
.modern-table tbody tr:nth-child(odd){ background: rgba(0,0,0,.02); }
.modern-table tbody tr:hover{ background: rgba(0,0,0,.04); }
</style>
@endsection
