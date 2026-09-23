<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">児童の参照顔写真</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-amber-50 border border-amber-200 text-amber-900 text-sm rounded-lg p-4">
                参照顔は顔認証の照合元です。保護者同意のうえで正面の鮮明な写真を1枚以上登録してください。
                ローカル開発では <code>FACE_RECOGNITION_DRIVER=fake</code>（同一画像のハッシュ一致）で動作確認できます。
            </div>

            @foreach ($children as $child)
                <div class="bg-white shadow-sm sm:rounded-lg p-5 border border-gray-100">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                        <h3 class="font-bold text-gray-900">#{{ $child->id }} {{ $child->name }}</h3>
                        <span class="text-xs text-gray-500">登録済み {{ $child->faceProfiles->count() }} 枚</span>
                    </div>

                    <ul class="text-sm text-gray-600 mb-4 space-y-1">
                        @forelse ($child->faceProfiles as $profile)
                            <li class="flex items-center justify-between gap-3 border-b border-gray-50 py-2">
                                <span>
                                    {{ $profile->is_primary ? '主写真' : '追加' }} /
                                    {{ $profile->external_face_id ?? '未インデックス' }} /
                                    {{ $profile->created_at?->format('Y-m-d H:i') }}
                                </span>
                                <form method="POST" action="{{ route('admin.children.faces.destroy', $profile) }}" onsubmit="return confirm('削除しますか？')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:underline text-xs">削除</button>
                                </form>
                            </li>
                        @empty
                            <li class="text-gray-400">まだ参照顔がありません。</li>
                        @endforelse
                    </ul>

                    <form method="POST" action="{{ route('admin.children.faces.store', $child) }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
                        @csrf
                        <div class="flex-1 min-w-[220px]">
                            <label class="block text-xs font-bold text-gray-700 mb-1">参照顔写真</label>
                            <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required
                                   class="w-full text-sm text-gray-500">
                        </div>
                        <button type="submit" class="bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold px-4 py-2 rounded-md">
                            登録する
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
