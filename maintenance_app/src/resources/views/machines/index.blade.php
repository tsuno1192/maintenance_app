@extends('layouts.app')

@section('title', '設備マスタ')

@section('content')
    <div class="tmq-page-head">
        <div>
            <h1>設備マスタ</h1>
            <p class="tmq-lead">現場の設備を登録し、申し送り・トラブルと紐付けます。</p>
        </div>
        <a class="tmq-btn tmq-btn--primary" href="{{ route('machines.create') }}">設備を登録</a>
    </div>

    <form method="get" action="{{ route('machines.index') }}" class="tmq-filter-row">
        <label class="tmq-field">
            <span>検索</span>
            <input type="search" name="q" value="{{ $q }}" placeholder="設備名・番号・型式">
        </label>
        <label class="tmq-field">
            <span>エリア</span>
            <select name="area">
                <option value="">すべて</option>
                @foreach ($areas as $item)
                    <option value="{{ $item }}" @selected($area === $item)>{{ $item }}</option>
                @endforeach
            </select>
        </label>
        <label class="tmq-field">
            <span>状態</span>
            <select name="status" onchange="this.form.submit()">
                <option value="">すべて</option>
                @foreach ($statuses as $item)
                    <option value="{{ $item->value }}" @selected($status === $item->value)>{{ $item->label() }}</option>
                @endforeach
            </select>
        </label>
    </form>

    <div class="tmq-table-wrap">
        <table class="tmq-table">
            <thead>
                <tr>
                    <th>設備番号</th>
                    <th>設備名</th>
                    <th>エリア</th>
                    <th>分類</th>
                    <th>状態</th>
                    <th>申し送り</th>
                    <th>トラブル</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($machines as $machine)
                    <tr>
                        <td>{{ $machine->code }}</td>
                        <td><a href="{{ route('machines.show', $machine) }}">{{ $machine->name }}</a></td>
                        <td>{{ $machine->area ?: '—' }}</td>
                        <td>{{ $machine->category ?: '—' }}</td>
                        <td><span class="tmq-badge">{{ $machine->status->label() }}</span></td>
                        <td>{{ $machine->memos_count }}</td>
                        <td>{{ $machine->troubles_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="tmq-empty">設備が登録されていません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $machines->links() }}
@endsection
