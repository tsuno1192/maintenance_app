@extends('layouts.app')

@section('title', 'Action List | タスク')
@section('heading', 'タスク一覧（音声対応）')

@section('content')
    <section
        id="voice-panel"
        class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
        data-voice-endpoint="{{ route('tasks.voice') }}"
        data-csrf="{{ csrf_token() }}"
    >
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold">音声操作</h2>
                <p class="mt-1 text-sm text-slate-600">
                    例: 「牛乳を買うを追加」「会議資料を完了して」「買い物を未完了」
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    id="voice-command-btn"
                    class="inline-flex items-center gap-2 rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800 disabled:opacity-50"
                >
                    <span aria-hidden="true">🎤</span>
                    <span data-label>音声コマンド</span>
                </button>
                <button
                    type="button"
                    id="voice-fill-btn"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50 disabled:opacity-50"
                >
                    <span aria-hidden="true">🎙️</span>
                    <span data-label>フォームへ音声入力</span>
                </button>
            </div>
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <div class="rounded-lg bg-slate-50 px-3 py-2 text-sm">
                <p class="text-xs font-semibold text-slate-500">認識結果</p>
                <p id="voice-transcript" class="mt-1 text-slate-800 min-h-[1.5rem]">—</p>
            </div>
            <div class="rounded-lg bg-slate-50 px-3 py-2 text-sm">
                <p class="text-xs font-semibold text-slate-500">ステータス</p>
                <p id="voice-status" class="mt-1 text-slate-800 min-h-[1.5rem]">待機中</p>
            </div>
        </div>

        <p id="voice-unsupported" class="mt-3 hidden text-sm text-amber-700">
            このブラウザは Web Speech API に対応していません。Chrome / Edge をご利用ください（HTTPS または localhost）。
        </p>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-semibold mb-4">新規タスク</h2>
        <form id="task-create-form" method="POST" action="{{ route('tasks.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="title" class="block text-sm font-medium text-slate-700">タイトル</label>
                <input
                    id="title"
                    name="title"
                    type="text"
                    value="{{ old('title') }}"
                    required
                    class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600"
                    placeholder="例: 牛乳を買う"
                >
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-slate-700">詳細</label>
                <textarea
                    id="description"
                    name="description"
                    rows="2"
                    class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600"
                >{{ old('description') }}</textarea>
            </div>
            <div>
                <label for="due_at" class="block text-sm font-medium text-slate-700">期限</label>
                <input
                    id="due_at"
                    name="due_at"
                    type="datetime-local"
                    value="{{ old('due_at') }}"
                    class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600"
                >
            </div>
            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                追加する
            </button>
        </form>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-semibold">一覧</h2>
            <div class="flex flex-wrap gap-2 text-sm">
                <a href="{{ route('tasks.index') }}" class="px-2 py-1 rounded {{ blank($status) ? 'bg-teal-100 text-teal-900' : 'text-slate-600 hover:bg-slate-100' }}">すべて</a>
                <a href="{{ route('tasks.index', ['status' => 'pending']) }}" class="px-2 py-1 rounded {{ $status === 'pending' ? 'bg-teal-100 text-teal-900' : 'text-slate-600 hover:bg-slate-100' }}">未完了</a>
                <a href="{{ route('tasks.index', ['status' => 'done']) }}" class="px-2 py-1 rounded {{ $status === 'done' ? 'bg-teal-100 text-teal-900' : 'text-slate-600 hover:bg-slate-100' }}">完了</a>
            </div>
        </div>

        @if ($tasks->isEmpty())
            <p class="text-sm text-slate-500">タスクはまだありません。音声またはフォームから追加してください。</p>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach ($tasks as $task)
                    <li class="py-3 flex flex-wrap items-start justify-between gap-3" data-task-id="{{ $task->id }}" data-task-title="{{ $task->title }}">
                        <div class="min-w-0 flex-1">
                            <p class="font-medium {{ $task->status === 'done' ? 'line-through text-slate-400' : 'text-slate-900' }}">
                                {{ $task->title }}
                            </p>
                            @if ($task->description)
                                <p class="mt-1 text-sm text-slate-500">{{ $task->description }}</p>
                            @endif
                            <p class="mt-1 text-xs text-slate-400">
                                #{{ $task->id }} / {{ $task->status }}
                                @if ($task->due_at)
                                    / 期限 {{ $task->due_at->format('Y-m-d H:i') }}
                                @endif
                            </p>
                        </div>
                        <form method="POST" action="{{ route('tasks.toggle', $task) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm hover:bg-slate-50">
                                {{ $task->status === 'done' ? '未完了に戻す' : '完了にする' }}
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
            <div class="mt-4">
                {{ $tasks->links() }}
            </div>
        @endif
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/voice-tasks.js') }}" defer></script>
@endpush
