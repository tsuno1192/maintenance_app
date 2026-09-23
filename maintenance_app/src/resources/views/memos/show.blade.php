@extends('layouts.app')

@section('title', $memo->title)

@section('content')
    <div class="tmq-page-head">
        <div>
            <h1>{{ $memo->title }}</h1>
            <p class="tmq-lead">
                <span @class(['tmq-badge', 'tmq-badge--warn' => $memo->priority->value !== 'normal'])>{{ $memo->priority->label() }}</span>
                {{ $memo->shift->label() }}
                / {{ $memo->user?->name }}
                / {{ $memo->created_at->format('Y-m-d H:i') }}
            </p>
        </div>
        <div class="tmq-actions" style="margin:0">
            <a class="tmq-btn tmq-btn--ghost" href="{{ route('memos.edit', $memo) }}">編集</a>
            <a class="tmq-btn tmq-btn--ghost" href="{{ route('memos.index') }}">一覧へ</a>
        </div>
    </div>

    <section class="tmq-card">
        <h2>内容</h2>
        <dl class="tmq-dl">
            <dt>関連設備</dt>
            <dd>
                @if ($memo->machine)
                    <a href="{{ route('machines.show', $memo->machine) }}">{{ $memo->machine->displayName() }}</a>
                @else
                    —
                @endif
            </dd>
            <dt>分類</dt>
            <dd>{{ $memo->category ?: '—' }}</dd>
            <dt>申し送り</dt>
            <dd>{{ $memo->body ?: '—' }}</dd>
            <dt>確認</dt>
            <dd>
                @if ($memo->isAcknowledged())
                    {{ $memo->acknowledgedByUser?->name }} / {{ $memo->acknowledged_at->format('Y-m-d H:i') }}
                @else
                    未確認
                @endif
            </dd>
        </dl>

        @unless ($memo->isAcknowledged())
            <form method="post" action="{{ route('memos.acknowledge', $memo) }}" class="tmq-actions" style="justify-content:flex-start">
                @csrf
                <button type="submit" class="tmq-btn tmq-btn--primary">確認した</button>
            </form>
        @endunless
    </section>

    @if ($memo->images->isNotEmpty())
        <section class="tmq-card">
            <h2>写真</h2>
            <div class="tmq-thumb-grid">
                @foreach ($memo->images as $image)
                    <figure>
                        <a href="{{ route('memos.images.show', [$memo, $image]) }}" target="_blank" rel="noopener">
                            <img src="{{ route('memos.images.show', [$memo, $image]) }}" alt="{{ $image->original_name }}">
                        </a>
                        <figcaption>
                            {{ $image->original_name }}
                            <form method="post" action="{{ route('memos.images.destroy', [$memo, $image]) }}" onsubmit="return confirm('この写真を削除しますか？');" style="display:inline">
                                @csrf
                                @method('delete')
                                <button type="submit" class="tmq-btn tmq-btn--ghost" style="padding:0.2rem 0.6rem">削除</button>
                            </form>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </section>
    @endif

    <form method="post" action="{{ route('memos.destroy', $memo) }}" onsubmit="return confirm('この申し送りを削除しますか？');">
        @csrf
        @method('delete')
        <button type="submit" class="tmq-btn tmq-btn--ghost">申し送りを削除</button>
    </form>
@endsection
