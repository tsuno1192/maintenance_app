<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">成長アルバム（AI選別）</h2></x-slot>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))<div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>@endif

            @if(auth()->user()->isStaff())
            <form method="POST" action="{{ route('albums.store') }}" class="bg-white p-6 rounded shadow-sm flex flex-wrap gap-3">
                @csrf
                <select name="child_id" required class="border-gray-300 rounded-md">
                    <option value="">児童</option>
                    @foreach($children as $child)<option value="{{ $child->id }}">{{ $child->name }}</option>@endforeach
                </select>
                <input type="month" name="year_month" value="{{ now()->format('Y-m') }}" required class="border-gray-300 rounded-md">
                <button class="bg-pink-600 text-white px-4 py-2 rounded text-sm font-semibold">ベストショット選別を実行</button>
            </form>
            @endif

            @foreach($albums as $album)
                <div class="bg-white shadow-sm rounded-lg p-5">
                    <div class="flex justify-between mb-3">
                        <h3 class="font-bold">{{ $album->title }}（{{ $album->child->name ?? '' }}）</h3>
                        <span class="text-xs text-gray-500">{{ $album->status }}</span>
                    </div>
                    <div class="grid md:grid-cols-3 gap-3">
                        @foreach($album->items as $item)
                            <div class="border rounded p-3 text-sm">
                                <div class="font-medium">{{ $item->documentation->ai_episode_title ?? '写真' }}</div>
                                <div class="text-xs text-gray-500 mt-1">score {{ number_format($item->score, 1) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
            <div>{{ $albums->links() }}</div>
        </div>
    </div>
</x-app-layout>
