<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                ダッシュボード
            </h2>
            <a href="{{ route('troubles.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                トラブル新規登録
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <p class="mb-6 text-gray-600">{{ auth()->user()->name }} さん、現場トラブルと現場ノートをまとめて確認できます。</p>

            <div class="tmq-stat-row tmq-stat-row--5">
                <a class="tmq-stat-link" href="{{ route('troubles.index') }}">
                    <strong>{{ $stats['troubles'] }}</strong>
                    <span>未完了トラブル</span>
                </a>
                <a class="tmq-stat-link" href="{{ route('todos.index') }}">
                    <strong>{{ $stats['todos'] }}</strong>
                    <span>未完了 TO DO</span>
                </a>
                <a class="tmq-stat-link" href="{{ route('memos.index', ['unacked' => 1]) }}">
                    <strong>{{ $stats['memos'] }}</strong>
                    <span>未確認申し送り</span>
                </a>
                <a class="tmq-stat-link" href="{{ route('tools.index', ['status' => 'in_use']) }}">
                    <strong>{{ $stats['tools'] }}</strong>
                    <span>貸出中の工具</span>
                </a>
                <a class="tmq-stat-link" href="{{ route('machines.index') }}">
                    <strong>{{ $stats['machines'] }}</strong>
                    <span>登録設備</span>
                </a>
            </div>

            <div class="tmq-grid tmq-grid--2" style="margin-top:1.25rem">
                <section class="tmq-card">
                    <div class="tmq-page-head" style="margin-bottom:0.75rem">
                        <h2 style="margin:0">最近のトラブル</h2>
                        <a href="{{ route('troubles.index') }}">一覧へ</a>
                    </div>
                    <ul class="tmq-inline-list">
                        @forelse ($recentTroubles as $trouble)
                            <li>
                                <a href="{{ route('troubles.show', $trouble) }}">{{ $trouble->title }}</a>
                                <span class="tmq-muted">
                                    {{ $trouble->status->label() }}
                                    @if ($trouble->machine)
                                        / {{ $trouble->machine->code }}
                                    @endif
                                </span>
                            </li>
                        @empty
                            <li class="tmq-muted">トラブルはまだありません。</li>
                        @endforelse
                    </ul>
                </section>

                <section class="tmq-card">
                    <div class="tmq-page-head" style="margin-bottom:0.75rem">
                        <h2 style="margin:0">最近の申し送り</h2>
                        <a href="{{ route('memos.index') }}">一覧へ</a>
                    </div>
                    <ul class="tmq-inline-list">
                        @forelse ($recentMemos as $memo)
                            <li>
                                <a href="{{ route('memos.show', $memo) }}">{{ $memo->title }}</a>
                                <span class="tmq-muted">
                                    {{ $memo->user?->name }}
                                    @if ($memo->isAcknowledged())
                                        / 確認済
                                    @else
                                        / 未確認
                                    @endif
                                </span>
                            </li>
                        @empty
                            <li class="tmq-muted">申し送りはまだありません。</li>
                        @endforelse
                    </ul>
                </section>
            </div>

            <section class="tmq-card">
                <h2>ショートカット</h2>
                <div class="tmq-actions" style="justify-content:flex-start;flex-wrap:wrap">
                    <a class="tmq-btn tmq-btn--primary" href="{{ route('memos.create') }}">申し送りを書く</a>
                    <a class="tmq-btn tmq-btn--ghost" href="{{ route('machines.create') }}">設備を登録</a>
                    <a class="tmq-btn tmq-btn--ghost" href="{{ route('tools.create') }}">工具を登録</a>
                    <a class="tmq-btn tmq-btn--ghost" href="{{ route('analytics.index') }}">集計・点検計画</a>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
