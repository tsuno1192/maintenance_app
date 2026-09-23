@extends('layouts.app')

@section('title', 'トラブル集計・点検計画')

@section('content')
    <div class="tmq-page-head">
        <div>
            <h1>トラブル集計・点検計画</h1>
            <p class="tmq-lead">大・中・小分類ごとの件数と発生頻度を集計し、Python Engine で推奨点検周期を算出します。</p>
        </div>
        <span class="tmq-badge {{ $engineOnline ? '' : 'tmq-badge--warn' }}">
            Python Engine: {{ $engineOnline ? 'オンライン' : 'オフライン' }}
        </span>
    </div>

    <section class="tmq-card">
        <form method="get" action="{{ route('analytics.index') }}" class="tmq-filter-row">
            <label class="tmq-field">
                <span>発生日 From</span>
                <input type="date" name="from" value="{{ $from }}">
            </label>
            <label class="tmq-field">
                <span>発生日 To</span>
                <input type="date" name="to" value="{{ $to }}">
            </label>
            <div class="tmq-actions" style="margin:0; align-self:end">
                <button type="submit" class="tmq-btn tmq-btn--ghost">集計する</button>
            </div>
        </form>

        <div class="tmq-stat-row">
            <div><strong>{{ $summary['total'] }}</strong><span>総件数</span></div>
            <div><strong>{{ $summary['categories'] }}</strong><span>分類組合せ</span></div>
            <div><strong>{{ $summary['top_major'] ?: '—' }}</strong><span>最多大分類</span></div>
        </div>
    </section>

    <section class="tmq-card">
        <div class="tmq-page-head" style="margin-bottom:0.75rem">
            <h2 style="margin:0">分類別集計</h2>
            <form method="post" action="{{ route('analytics.plan') }}">
                @csrf
                <input type="hidden" name="from" value="{{ $from }}">
                <input type="hidden" name="to" value="{{ $to }}">
                <button type="submit" class="tmq-btn tmq-btn--primary" @disabled(! $engineOnline || $aggregates->isEmpty())>
                    点検周期を自動計画
                </button>
            </form>
        </div>

        <div class="tmq-table-wrap">
            <table class="tmq-table">
                <thead>
                    <tr>
                        <th>大分類</th>
                        <th>中分類</th>
                        <th>小分類</th>
                        <th>件数</th>
                        <th>初回発生</th>
                        <th>最終発生</th>
                        <th>期間(日)</th>
                        <th>頻度(/月)</th>
                        <th>平均間隔(日)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($aggregates as $row)
                        <tr>
                            <td>{{ $row['category_major'] }}</td>
                            <td>{{ $row['category_middle'] }}</td>
                            <td>{{ $row['category_minor'] }}</td>
                            <td>{{ $row['count'] }}</td>
                            <td>{{ $row['first_occurred_on'] ?? '—' }}</td>
                            <td>{{ $row['last_occurred_on'] ?? '—' }}</td>
                            <td>{{ $row['span_days'] }}</td>
                            <td>{{ number_format($row['frequency_per_month'], 2) }}</td>
                            <td>{{ $row['avg_days_between'] ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="tmq-empty">集計対象のデータがありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if (! empty($plans))
        <section class="tmq-card">
            <h2>推奨点検計画（Python Engine）</h2>
            @if (! empty($planMeta))
                <p class="tmq-hint">
                    生成: {{ $planMeta['generated_at'] ?? '—' }}
                    ／ 監視データ: {{ $planMeta['monitoring_source'] ?? '—' }}
                    @if (! empty($planMeta['notes']))
                        ／ {{ $planMeta['notes'] }}
                    @endif
                </p>
            @endif

            <div class="tmq-table-wrap">
                <table class="tmq-table">
                    <thead>
                        <tr>
                            <th>大分類</th>
                            <th>中分類</th>
                            <th>小分類</th>
                            <th>推奨点検周期(日)</th>
                            <th>推奨点検箇所</th>
                            <th>リスクスコア</th>
                            <th>根拠</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($plans as $plan)
                            <tr>
                                <td>{{ $plan['category_major'] ?? '—' }}</td>
                                <td>{{ $plan['category_middle'] ?? '—' }}</td>
                                <td>{{ $plan['category_minor'] ?? '—' }}</td>
                                <td><strong>{{ $plan['recommended_cycle_days'] ?? '—' }}</strong></td>
                                <td>
                                    @if (! empty($plan['recommended_points']))
                                        <ul class="tmq-inline-list">
                                            @foreach ($plan['recommended_points'] as $point)
                                                <li>{{ $point }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ isset($plan['risk_score']) ? number_format((float) $plan['risk_score'], 2) : '—' }}</td>
                                <td>{{ $plan['rationale'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection
