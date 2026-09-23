<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">顔認証レビュー（手動紐付け）</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-4 text-sm text-gray-600">
                自動推定できなかった写真、または確認が必要な写真をここで正しい児童に紐付けます。
                <a href="{{ route('documentations.index') }}" class="text-pink-700 hover:underline ml-2">ドキュメンテーションへ戻る</a>
            </div>

            @forelse ($documentations as $doc)
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden border border-gray-100">
                    <div class="md:flex">
                        <img src="{{ route('documentations.photo', $doc) }}" alt="" class="w-full md:w-64 h-48 object-cover">
                        <div class="p-5 flex-1 space-y-3">
                            <div class="text-sm text-gray-500">
                                投稿: {{ $doc->user->name ?? '-' }} /
                                状態: <strong>{{ $doc->face_match_status }}</strong>
                                @if (!is_null($doc->face_match_confidence))
                                    / 信頼度 {{ number_format($doc->face_match_confidence, 1) }}%
                                @endif
                            </div>
                            <div class="text-sm">
                                現在の児童:
                                <strong>{{ $doc->child->name ?? '未設定' }}</strong>
                                @if ($doc->suggestedChild)
                                    <span class="text-gray-500">（推定: {{ $doc->suggestedChild->name }}）</span>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('admin.documentations.assign', $doc) }}" class="flex flex-wrap items-end gap-3">
                                @csrf
                                @method('PATCH')
                                <div class="flex-1 min-w-[200px]">
                                    <label class="block text-xs font-bold text-gray-700 mb-1">正しい児童ID</label>
                                    <select name="child_id" required class="w-full border-gray-300 rounded-md text-sm">
                                        <option value="">選択してください</option>
                                        @foreach ($children as $child)
                                            <option value="{{ $child->id }}" @selected(($doc->suggested_child_id ?? $doc->child_id) == $child->id)>
                                                #{{ $child->id }} {{ $child->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="bg-slate-900 text-white text-sm font-semibold px-4 py-2 rounded-md">
                                    紐付けを保存
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white p-8 text-center text-gray-500 rounded-lg">レビュー待ちの写真はありません。</div>
            @endforelse

            <div>{{ $documentations->links() }}</div>
        </div>
    </div>
</x-app-layout>
