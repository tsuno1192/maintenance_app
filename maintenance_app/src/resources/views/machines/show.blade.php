@extends('layouts.app')

@section('title', $machine->name)

@section('content')
    <div class="tmq-page-head">
        <div>
            <h1>{{ $machine->name }}</h1>
            <p class="tmq-lead">
                <span class="tmq-badge">{{ $machine->status->label() }}</span>
                {{ $machine->code }}
                @if ($machine->area) / {{ $machine->area }} @endif
            </p>
        </div>
        <div class="tmq-actions" style="margin:0">
            <a class="tmq-btn tmq-btn--primary" href="{{ route('machines.edit', $machine) }}">編集</a>
            <a class="tmq-btn tmq-btn--ghost" href="{{ route('machines.index') }}">一覧へ</a>
        </div>
    </div>

    <div class="tmq-grid tmq-grid--2">
        <section class="tmq-card">
            <h2>設備情報</h2>
            <dl class="tmq-dl">
                <dt>分類</dt>
                <dd>{{ $machine->category ?: '—' }}</dd>
                <dt>メーカー / 型式</dt>
                <dd>{{ $machine->manufacturer ?: '—' }} / {{ $machine->model ?: '—' }}</dd>
                <dt>設置日</dt>
                <dd>{{ optional($machine->installed_on)->format('Y-m-d') ?? '—' }}</dd>
                <dt>備考</dt>
                <dd>{{ $machine->notes ?: '—' }}</dd>
            </dl>
        </section>

        <section class="tmq-card">
            <h2>関連トラブル</h2>
            <ul class="tmq-inline-list">
                @forelse ($machine->troubles as $trouble)
                    <li>
                        <a href="{{ route('troubles.show', $trouble) }}">{{ $trouble->title }}</a>
                        <span class="tmq-muted">{{ $trouble->status->label() }}</span>
                    </li>
                @empty
                    <li class="tmq-muted">関連トラブルはありません。</li>
                @endforelse
            </ul>
            <div class="tmq-actions" style="justify-content:flex-start">
                <a class="tmq-btn tmq-btn--ghost" href="{{ route('troubles.create') }}">トラブルを登録</a>
            </div>
        </section>
    </div>

    <section class="tmq-card">
        <h2>関連申し送り</h2>
        <ul class="tmq-inline-list">
            @forelse ($machine->memos as $memo)
                <li>
                    <a href="{{ route('memos.show', $memo) }}">{{ $memo->title }}</a>
                    <span class="tmq-muted">{{ $memo->user?->name }} / {{ $memo->created_at->format('Y-m-d H:i') }}</span>
                </li>
            @empty
                <li class="tmq-muted">申し送りはありません。</li>
            @endforelse
        </ul>
        <div class="tmq-actions" style="justify-content:flex-start">
            <a class="tmq-btn tmq-btn--primary" href="{{ route('memos.create', ['machine_id' => $machine->id]) }}">申し送りを書く</a>
        </div>
    </section>

    <form method="post" action="{{ route('machines.destroy', $machine) }}" onsubmit="return confirm('この設備を削除しますか？関連する申し送りの設備紐付けは解除されます。');">
        @csrf
        @method('delete')
        <button type="submit" class="tmq-btn tmq-btn--ghost">設備を削除</button>
    </form>
@endsection
