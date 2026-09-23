@extends('layouts.app')

@section('title', $tool->name)

@section('content')
    <div class="tmq-page-head">
        <div>
            <h1>{{ $tool->name }}</h1>
            <p class="tmq-lead">
                <span class="tmq-badge">{{ $tool->status->label() }}</span>
                {{ $tool->code }}
                @if ($tool->location) / {{ $tool->location }} @endif
            </p>
        </div>
        <div class="tmq-actions" style="margin:0">
            <a class="tmq-btn tmq-btn--primary" href="{{ route('tools.edit', $tool) }}">編集</a>
            <a class="tmq-btn tmq-btn--ghost" href="{{ route('tools.index') }}">一覧へ</a>
        </div>
    </div>

    <div class="tmq-grid tmq-grid--2">
        <section class="tmq-card">
            <h2>工具情報</h2>
            <dl class="tmq-dl">
                <dt>分類</dt>
                <dd>{{ $tool->category ?: '—' }}</dd>
                <dt>在庫数</dt>
                <dd>{{ $tool->quantity }}</dd>
                <dt>メーカー</dt>
                <dd>{{ $tool->manufacturer ?: '—' }}</dd>
                <dt>購入日</dt>
                <dd>{{ optional($tool->purchased_on)->format('Y-m-d') ?? '—' }}</dd>
                <dt>備考</dt>
                <dd>{{ $tool->notes ?: '—' }}</dd>
            </dl>
        </section>

        <section class="tmq-card">
            <h2>貸出・返却・点検</h2>
            <form method="post" action="{{ route('tools.logs.store', $tool) }}" class="tmq-form">
                @csrf
                <label class="tmq-field">
                    <span>操作 <em>*</em></span>
                    <select name="action" required>
                        @foreach ($actions as $action)
                            <option value="{{ $action->value }}">{{ $action->label() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="tmq-field">
                    <span>数量 <em>*</em></span>
                    <input type="number" name="quantity" min="1" value="1" required>
                </label>
                <label class="tmq-field">
                    <span>メモ</span>
                    <input type="text" name="note" placeholder="用途・返却予定など">
                </label>
                <button type="submit" class="tmq-btn tmq-btn--primary">履歴を記録</button>
            </form>
        </section>
    </div>

    <section class="tmq-card">
        <h2>履歴</h2>
        <div class="tmq-table-wrap" style="box-shadow:none">
            <table class="tmq-table">
                <thead>
                    <tr>
                        <th>日時</th>
                        <th>操作</th>
                        <th>数量</th>
                        <th>担当</th>
                        <th>メモ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tool->logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $log->action->label() }}</td>
                            <td>{{ $log->quantity }}</td>
                            <td>{{ $log->user?->name ?: '—' }}</td>
                            <td>{{ $log->note ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="tmq-empty">履歴はまだありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <form method="post" action="{{ route('tools.destroy', $tool) }}" onsubmit="return confirm('この工具を削除しますか？履歴も削除されます。');">
        @csrf
        @method('delete')
        <button type="submit" class="tmq-btn tmq-btn--ghost">工具を削除</button>
    </form>
@endsection
