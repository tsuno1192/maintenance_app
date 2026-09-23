<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">連絡帳（AI要約）</h2></x-slot>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))<div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>@endif

            @if(auth()->user()->isStaff())
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-bold mb-3">メモ入力 → AIが保護者向け文面に整える</h3>
                <form method="POST" action="{{ route('contact-notes.store') }}" class="space-y-3">
                    @csrf
                    <select name="child_id" required class="w-full border-gray-300 rounded-md">
                        <option value="">児童を選択</option>
                        @foreach($children as $child)<option value="{{ $child->id }}">{{ $child->name }}</option>@endforeach
                    </select>
                    <input type="date" name="note_date" value="{{ now()->toDateString() }}" required class="w-full border-gray-300 rounded-md">
                    <textarea name="raw_memo" rows="4" required class="w-full border-gray-300 rounded-md" placeholder="例:&#10;- 午前中外遊び&#10;- 給食完食&#10;- 午睡順調"></textarea>
                    <button class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-semibold">登録してAI要約</button>
                </form>
            </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg divide-y">
                @forelse($notes as $note)
                    <div class="p-5">
                        <div class="text-xs text-gray-500 mb-1">{{ $note->child->name ?? '-' }} / {{ $note->note_date?->format('Y-m-d') }} / {{ $note->status }}</div>
                        <p class="text-sm text-gray-500 whitespace-pre-line">{{ $note->raw_memo }}</p>
                        @if($note->polished_body)
                            <p class="mt-2 text-gray-900 whitespace-pre-line">{{ $note->polished_body }}</p>
                        @endif
                    </div>
                @empty
                    <div class="p-6 text-gray-500 text-sm">連絡帳はまだありません。</div>
                @endforelse
            </div>
            <div>{{ $notes->links() }}</div>
        </div>
    </div>
</x-app-layout>
