<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('AIドキュメンテーション（成長ポートフォリオ）') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
            @endif

            @if(auth()->user()->isStaff())
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-t-4 border-pink-500">
                <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">写真からエピソードを自動生成</h3>
                        <p class="text-sm text-gray-500">児童を未選択のままアップロードすると、顔認証で自動紐付けを試行します。</p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-sm">
                        <a href="{{ route('admin.documentations.review') }}" class="text-pink-700 hover:underline">紐付けレビュー</a>
                        <a href="{{ route('admin.children.faces') }}" class="text-pink-700 hover:underline">参照顔の登録</a>
                    </div>
                </div>

                <form action="{{ route('documentations.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">対象の児童（任意）</label>
                            <select name="child_id" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                <option value="">未選択（顔認証で自動推定）</option>
                                @foreach($children ?? [] as $child)
                                <option value="{{ $child->id }}" @selected(old('child_id')==$child->id)>{{ $child->name }}</option>
                                @endforeach
                            </select>
                            @error('child_id')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">活動の写真</label>
                            <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100" required>
                            @error('photo')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="auto_face_match" value="1" class="rounded border-gray-300" checked>
                        児童未選択時は顔認証で自動紐付けする
                    </label>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-pink-600 hover:bg-pink-700 text-white text-sm font-bold py-2 px-6 rounded-md shadow transition">
                            アップロードする
                        </button>
                    </div>
                </form>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($documentations ?? [] as $doc)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex flex-col justify-between border border-gray-100 hover:shadow-md transition">
                    <div>
                        <img src="{{ route('documentations.photo', $doc) }}" alt="Activity Photo" class="w-full h-56 object-cover">
                        <div class="p-5">
                            <div class="flex justify-between items-center mb-2 gap-2">
                                <span class="text-xs font-bold bg-pink-100 text-pink-800 px-2.5 py-0.5 rounded-full">{{ $doc->non_cognitive_skill ?? '成長記録' }}</span>
                                <span class="text-xs text-gray-400">
                                    {{ $doc->child->name ?? '未紐付け' }} / {{ $doc->created_at->format('Y.m.d') }}
                                </span>
                            </div>
                            @if(auth()->user()->isStaff())
                            <p class="text-xs mb-2">
                                @php
                                $badge = match ($doc->face_match_status) {
                                'matched' => 'bg-emerald-100 text-emerald-800',
                                'pending' => 'bg-amber-100 text-amber-800',
                                'unmatched' => 'bg-red-100 text-red-800',
                                'manual' => 'bg-blue-100 text-blue-800',
                                default => 'bg-gray-100 text-gray-700',
                                };
                                @endphp
                                <span class="px-2 py-0.5 rounded {{ $badge }}">顔認証: {{ $doc->face_match_status }}</span>
                                @if (!is_null($doc->face_match_confidence))
                                <span class="text-gray-400 ml-1">{{ number_format($doc->face_match_confidence, 1) }}%</span>
                                @endif
                            </p>
                            @endif
                            <h4 class="font-bold text-gray-900 text-base mb-2">{{ $doc->ai_episode_title }}</h4>
                            <p class="text-sm text-gray-600 whitespace-pre-line leading-relaxed">{{ $doc->ai_body }}</p>
                        </div>
                    </div>
                    <div class="px-5 pb-4 pt-2 border-t border-gray-50 text-xs text-gray-400 flex justify-between items-center">
                        <span>記録者: {{ $doc->user->name ?? 'スタッフ' }}</span>

                        <div class="flex items-center gap-3">
                            @if(auth()->user()->isStaff() && $doc->needsFaceReview())
                            <a href="{{ route('admin.documentations.review') }}" class="text-pink-600 font-semibold hover:underline">手動修正</a>
                            @endif

                            @if(auth()->user()->isStaff())
                            <!-- 削除ボタン（フォーム） -->
                            <form action="{{ route('documentations.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('本当にこの写真を削除しますか？');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 font-semibold">
                                    削除
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full bg-white p-8 text-center rounded-lg text-gray-500">
                    まだドキュメンテーションがありません。上のフォームから写真をアップロードしてみましょう！
                </div>
                @endforelse
            </div>

            @if (method_exists($documentations, 'links'))
            <div class="mt-4">{{ $documentations->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>