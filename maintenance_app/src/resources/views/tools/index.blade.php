@extends('layouts.app')

@section('title', '工具管理')

@section('content')
    <div class="tmq-page-head">
        <div>
            <h1>工具管理</h1>
            <p class="tmq-lead">工具の在庫・貸出・返却・点検履歴を管理します。</p>
        </div>
        <a class="tmq-btn tmq-btn--primary" href="{{ route('tools.create') }}">工具を登録</a>
    </div>

    <form method="get" action="{{ route('tools.index') }}" class="tmq-filter-row">
        <label class="tmq-field">
            <span>検索</span>
            <input type="search" name="q" value="{{ $q }}" placeholder="名称・管理番号・保管場所">
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
        <div class="tmq-actions" style="margin:0;align-self:end">
            <button type="submit" class="tmq-btn tmq-btn--ghost">絞り込む</button>
        </div>
    </form>

    <div class="tmq-table-wrap">
        <table class="tmq-table">
            <thead>
                <tr>
                    <th>管理番号</th>
                    <th>名称</th>
                    <th>分類</th>
                    <th>保管場所</th>
                    <th>状態</th>
                    <th>在庫</th>
                    <th>履歴</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tools as $tool)
                    <tr>
                        <td>{{ $tool->code }}</td>
                        <td><a href="{{ route('tools.show', $tool) }}">{{ $tool->name }}</a></td>
                        <td>{{ $tool->category ?: '—' }}</td>
                        <td>{{ $tool->location ?: '—' }}</td>
                        <td><span class="tmq-badge">{{ $tool->status->label() }}</span></td>
                        <td>{{ $tool->quantity }}</td>
                        <td>{{ $tool->logs_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="tmq-empty">工具が登録されていません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $tools->links() }}
@endsection
