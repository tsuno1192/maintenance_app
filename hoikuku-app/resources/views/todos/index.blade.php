<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Action List タスク連携（音声対応）
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                    {{ session('status') }}
                </div>
            @endif

            @if ($error)
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                    {{ $error }}
                </div>
            @endif

            @error('todo')
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                    {{ $message }}
                </div>
            @enderror

            <div
                id="voice-panel"
                class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                data-voice-endpoint="{{ route('todos.voice') }}"
                data-csrf="{{ csrf_token() }}"
            >
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">音声操作</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            例: 「牛乳を買うを追加」「会議資料を完了して」「買い物を未完了」
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" id="voice-command-btn"
                                class="inline-flex items-center px-4 py-2 bg-teal-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-teal-800">
                            <span data-label>音声コマンド</span>
                        </button>
                        <button type="button" id="voice-fill-btn"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                            <span data-label>フォームへ音声入力</span>
                        </button>
                    </div>
                </div>
                <div class="mt-4 grid gap-3 md:grid-cols-2 text-sm">
                    <div class="bg-gray-50 rounded px-3 py-2">
                        <div class="text-xs text-gray-500">認識結果</div>
                        <div id="voice-transcript" class="mt-1">—</div>
                    </div>
                    <div class="bg-gray-50 rounded px-3 py-2">
                        <div class="text-xs text-gray-500">ステータス</div>
                        <div id="voice-status" class="mt-1">待機中</div>
                    </div>
                </div>
                <p id="voice-unsupported" class="hidden mt-3 text-sm text-amber-700">
                    このブラウザは Web Speech API 非対応です。Chrome / Edge（HTTPS または localhost）を使ってください。
                </p>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">新規タスク作成</h3>
                <form id="task-create-form" method="POST" action="{{ route('todos.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="title">タイトル</label>
                        <input id="title" name="title" type="text" value="{{ old('title') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @error('title')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="description">詳細</label>
                        <textarea id="description" name="description" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="due_at">期限</label>
                        <input id="due_at" name="due_at" type="datetime-local" value="{{ old('due_at') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        todo-app に作成
                    </button>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">タスク一覧</h3>
                    @if (!empty($meta['total']))
                        <span class="text-sm text-gray-500">全 {{ $meta['total'] }} 件</span>
                    @endif
                </div>

                @if (empty($tasks))
                    <p class="text-gray-500 text-sm">表示できるタスクはありません。</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b text-left text-gray-600">
                                    <th class="py-2 pr-4">ID</th>
                                    <th class="py-2 pr-4">タイトル</th>
                                    <th class="py-2 pr-4">状態</th>
                                    <th class="py-2 pr-4">期限</th>
                                    <th class="py-2">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tasks as $task)
                                    <tr class="border-b border-gray-100">
                                        <td class="py-2 pr-4 text-gray-500">{{ $task['id'] ?? '-' }}</td>
                                        <td class="py-2 pr-4">
                                            <div class="font-medium text-gray-900 {{ ($task['status'] ?? '') === 'done' ? 'line-through text-gray-400' : '' }}">
                                                {{ $task['title'] ?? '-' }}
                                            </div>
                                            @if (!empty($task['description']))
                                                <div class="text-xs text-gray-500 mt-1">{{ $task['description'] }}</div>
                                            @endif
                                        </td>
                                        <td class="py-2 pr-4">{{ $task['status'] ?? '-' }}</td>
                                        <td class="py-2 pr-4">{{ $task['due_at'] ?? '-' }}</td>
                                        <td class="py-2">
                                            @if (!empty($task['id']))
                                                <form method="POST" action="{{ route('todos.toggle', $task['id']) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-indigo-700 hover:underline">
                                                        {{ ($task['status'] ?? '') === 'done' ? '未完了へ' : '完了へ' }}
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="{{ asset('js/voice-tasks.js') }}" defer></script>
</x-app-layout>
