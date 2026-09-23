@extends('layouts.app')

@section('title', '完了報告詳細')

@section('content')
    <div class="tmq-page-head">
        <div>
            <h1>{{ $report->title }}</h1>
            <p class="tmq-lead">
                <span class="tmq-badge">{{ $report->status->label() }}</span>
                関連トラブル:
                <a href="{{ route('troubles.show', $report->trouble) }}">#{{ $report->trouble_id }} {{ $report->trouble->title }}</a>
            </p>
        </div>
        <a class="tmq-btn tmq-btn--ghost" href="{{ route('troubles.show', $report->trouble) }}">トラブルへ戻る</a>
    </div>

    <section class="tmq-card">
        <h2>完了報告ワークフロー</h2>
        <ol class="tmq-steps">
            @foreach (\App\Enums\RepairReportStatus::workflowSteps() as $step)
                <li @class([
                    'is-done' => $report->status->isAfter($step),
                    'is-current' => $report->status === $step,
                ])>
                    <strong>{{ $step->label() }}</strong>
                    @php
                        $flag = $step->approvalFlagColumn();
                        $at = $step->approvalAtColumn();
                    @endphp
                    @if ($flag && $report->{$flag})
                        <span class="tmq-muted">承認済{{ $at && $report->{$at} ? ' @ '.$report->{$at}->format('Y-m-d H:i') : '' }}</span>
                    @elseif ($report->status === $step)
                        <span class="tmq-muted">対応待ち</span>
                    @endif
                </li>
            @endforeach
        </ol>

        @if ($report->status->canAdvance())
            <form method="post" action="{{ route('repair-reports.approve', $report) }}" class="tmq-actions" onsubmit="return confirm('このステップを承認して次へ進めますか？');">
                @csrf
                <button type="submit" class="tmq-btn tmq-btn--primary">
                    {{ $report->status->approveButtonLabel() }}
                </button>
            </form>
        @else
            <p class="tmq-hint">この完了報告の承認ワークフローは完了しています。</p>
        @endif
    </section>

    <div class="tmq-grid tmq-grid--2">
        <section class="tmq-card">
            <h2>報告内容</h2>
            <dl class="tmq-dl">
                <dt>分類</dt>
                <dd>
                    {{ $report->category_major }}
                    @if ($report->category_middle) / {{ $report->category_middle }} @endif
                    @if ($report->category_minor) / {{ $report->category_minor }} @endif
                </dd>
                <dt>内容</dt>
                <dd>{{ $report->content ?: '—' }}</dd>
                <dt>調査結果</dt>
                <dd>{{ $report->investigation_result ?: '—' }}</dd>
                <dt>発生原因</dt>
                <dd>{{ $report->cause ?: '—' }}</dd>
            </dl>
        </section>

        <section class="tmq-card">
            <h2>補修・動作確認</h2>
            <dl class="tmq-dl">
                <dt>補修日</dt>
                <dd>{{ optional($report->repaired_on)->format('Y-m-d') ?? '—' }}</dd>
                <dt>使用予備品</dt>
                <dd>{{ $report->used_spare_parts ?: '—' }}</dd>
                <dt>使用図面</dt>
                <dd>{{ $report->used_drawings ?: '—' }}</dd>
                <dt>試運転結果</dt>
                <dd>{{ $report->trial_run_result ?: '—' }}</dd>
                <dt>各種動作記録</dt>
                <dd>{{ $report->operation_records ?: '—' }}</dd>
            </dl>
        </section>
    </div>
@endsection
