@extends('layouts.app')

@section('title', 'トラブル詳細')

@section('content')
    <div class="tmq-page-head">
        <div>
            <h1>{{ $trouble->title }}</h1>
            <p class="tmq-lead">
                <span class="tmq-badge">{{ $trouble->status->label() }}</span>
                {{ $trouble->category_major }}
                @if ($trouble->category_middle) / {{ $trouble->category_middle }} @endif
                @if ($trouble->category_minor) / {{ $trouble->category_minor }} @endif
            </p>
        </div>
        <div class="tmq-actions" style="margin:0">
            <a class="tmq-btn tmq-btn--primary" href="{{ route('troubles.pdf', $trouble) }}">保全依頼表 PDF</a>
            <a class="tmq-btn tmq-btn--ghost" href="{{ route('troubles.index') }}">一覧へ</a>
        </div>
    </div>

    <section class="tmq-card">
        <h2>承認ワークフロー</h2>
        <ol class="tmq-steps">
            @foreach (\App\Enums\TroubleStatus::workflowSteps() as $step)
                <li @class([
                    'is-done' => $trouble->status->isAfter($step),
                    'is-current' => $trouble->status === $step,
                ])>
                    <strong>{{ $step->label() }}</strong>
                    @php
                        $flag = $step->approvalFlagColumn();
                        $at = $step->approvalAtColumn();
                    @endphp
                    @if ($flag && $trouble->{$flag})
                        <span class="tmq-muted">承認済{{ $at && $trouble->{$at} ? ' @ '.$trouble->{$at}->format('Y-m-d H:i') : '' }}</span>
                    @elseif ($trouble->status === $step)
                        <span class="tmq-muted">対応待ち</span>
                    @endif
                </li>
            @endforeach
        </ol>

        @if ($trouble->status->canAdvance())
            <form method="post" action="{{ route('troubles.approve', $trouble) }}" class="tmq-actions" onsubmit="return confirm('このステップを承認して次へ進めますか？');">
                @csrf
                <button type="submit" class="tmq-btn tmq-btn--primary">
                    {{ $trouble->status->approveButtonLabel() }}
                </button>
            </form>
        @else
            <p class="tmq-hint">このトラブルの承認ワークフローは完了しています。</p>
        @endif
    </section>

    <section class="tmq-card">
        <h2>現場補修・完了報告</h2>
        @if ($trouble->repairReport)
            <p class="tmq-lead">完了報告が登録済みです（状態: {{ $trouble->repairReport->status->label() }}）。</p>
            <div class="tmq-actions" style="justify-content:flex-start">
                <a class="tmq-btn tmq-btn--primary" href="{{ route('repair-reports.show', $trouble->repairReport) }}">完了報告を開く</a>
            </div>
        @else
            <p class="tmq-lead">現場補修が完了したら、完了報告を入力してください。保存後に承認ワークフローが開始されます。</p>
            <div class="tmq-actions" style="justify-content:flex-start">
                <a class="tmq-btn tmq-btn--primary" href="{{ route('repair-reports.create', $trouble) }}">完了報告を入力する</a>
            </div>
        @endif
    </section>

    <div class="tmq-grid tmq-grid--2">
        <section class="tmq-card">
            <h2>内容</h2>
            <dl class="tmq-dl">
                <dt>内容</dt>
                <dd>{{ $trouble->content ?: '—' }}</dd>
                <dt>調査内容</dt>
                <dd>{{ $trouble->investigation ?: '—' }}</dd>
                <dt>推定原因</dt>
                <dd>{{ $trouble->estimated_cause ?: '—' }}</dd>
                <dt>必要予備品</dt>
                <dd>{{ $trouble->required_spare_parts ?: '—' }}</dd>
                <dt>必要図面</dt>
                <dd>{{ $trouble->required_drawings ?: '—' }}</dd>
            </dl>
        </section>

        <section class="tmq-card">
            <h2>管理情報</h2>
            <dl class="tmq-dl">
                <dt>発生日</dt>
                <dd>{{ optional($trouble->occurred_on)->format('Y-m-d') ?? '—' }}</dd>
                <dt>補修依頼日</dt>
                <dd>{{ optional($trouble->repair_requested_on)->format('Y-m-d') ?? '—' }}</dd>
                <dt>作成Gr</dt>
                <dd>{{ $trouble->created_group ?: '—' }}</dd>
                <dt>関連設備</dt>
                <dd>
                    @if ($trouble->machine)
                        <a href="{{ route('machines.show', $trouble->machine) }}">{{ $trouble->machine->displayName() }}</a>
                    @else
                        —
                    @endif
                </dd>
                <dt>報告者</dt>
                <dd>{{ $trouble->reporter_name ?: '—' }}</dd>
            </dl>

            <h2 class="tmq-subhead">連動 TO DO</h2>
            <ul class="tmq-todo-list">
                @forelse ($trouble->todos as $todo)
                    <li>
                        <strong>{{ $todo->group_name }}</strong>
                        — {{ $todo->title }}
                        @if ($todo->is_completed)
                            <span class="tmq-badge">完了</span>
                        @endif
                        @if ($todo->due_on)
                            <span class="tmq-muted">（期限: {{ $todo->due_on->format('Y-m-d') }}）</span>
                        @endif
                        <a href="{{ route('todos.index', ['group' => $todo->group_name]) }}">リストを見る</a>
                    </li>
                @empty
                    <li class="tmq-muted">TO DO はありません。</li>
                @endforelse
            </ul>
        </section>
    </div>
@endsection
