@extends('layouts.app')

@section('title', '現場申し送り')

@section('content')
    <div class="tmq-page-head">
        <div>
            <h1>現場申し送り</h1>
            <p class="tmq-lead">シフト間の申し送りと写真を共有します。</p>
        </div>
        <a class="tmq-btn tmq-btn--primary" href="{{ route('memos.create') }}">申し送りを書く</a>
    </div>

    <form method="get" action="{{ route('memos.index') }}" class="tmq-filter-row">
        <label class="tmq-field">
            <span>重要度</span>
            <select name="priority" onchange="this.form.submit()">
                <option value="">すべて</option>
                @foreach ($priorities as $item)
                    <option value="{{ $item->value }}" @selected($priority === $item->value)>{{ $item->label() }}</option>
                @endforeach
            </select>
        </label>
        <label class="tmq-field" style="flex-direction:row;align-items:center;gap:0.5rem;margin-top:1.6rem">
            <input type="checkbox" name="unacked" value="1" @checked($unacked) onchange="this.form.submit()">
            <span>未確認のみ</span>
        </label>
    </form>

    <div class="tmq-table-wrap">
        <table class="tmq-table">
            <thead>
                <tr>
                    <th>日時</th>
                    <th>件名</th>
                    <th>設備</th>
                    <th>勤務帯</th>
                    <th>重要度</th>
                    <th>作成者</th>
                    <th>確認</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($memos as $memo)
                    <tr>
                        <td>{{ $memo->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('memos.show', $memo) }}">{{ $memo->title }}</a>
                            @if ($memo->images_count)
                                <span class="tmq-muted">（写真 {{ $memo->images_count }}）</span>
                            @endif
                        </td>
                        <td>
                            @if ($memo->machine)
                                <a href="{{ route('machines.show', $memo->machine) }}">{{ $memo->machine->code }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $memo->shift->label() }}</td>
                        <td>
                            <span @class(['tmq-badge', 'tmq-badge--warn' => $memo->priority->value !== 'normal'])>
                                {{ $memo->priority->label() }}
                            </span>
                        </td>
                        <td>{{ $memo->user?->name ?: '—' }}</td>
                        <td>{{ $memo->isAcknowledged() ? '確認済' : '未確認' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="tmq-empty">申し送りはありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $memos->links() }}
@endsection
