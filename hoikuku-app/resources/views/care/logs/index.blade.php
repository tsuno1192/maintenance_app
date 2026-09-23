<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">おむつ・ミルク・食事タイムライン</h2></x-slot>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))<div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>@endif

            <form method="GET" class="bg-white p-4 rounded shadow-sm inline-flex gap-2 items-end">
                <input type="date" name="date" value="{{ $date }}" class="border-gray-300 rounded-md">
                <button class="bg-gray-800 text-white px-3 py-2 rounded text-sm">表示</button>
            </form>

            @if(auth()->user()->isStaff())
            <form method="POST" action="{{ route('care-logs.store') }}" class="bg-white p-6 rounded shadow-sm grid md:grid-cols-5 gap-3">
                @csrf
                <select name="child_id" required class="border-gray-300 rounded-md">
                    <option value="">児童</option>
                    @foreach($children as $child)<option value="{{ $child->id }}">{{ $child->name }}</option>@endforeach
                </select>
                <select name="type" id="care-type" class="border-gray-300 rounded-md">
                    <option value="diaper">おむつ</option>
                    <option value="milk">ミルク</option>
                    <option value="meal">食事</option>
                </select>
                <select name="diaper_status" class="border-gray-300 rounded-md">
                    <option value="wet">おしっこ</option>
                    <option value="dirty">うんち</option>
                    <option value="both">両方</option>
                    <option value="dry">交換のみ</option>
                </select>
                <input type="number" name="milk_ml" placeholder="ml" class="border-gray-300 rounded-md">
                <select name="meal_amount" class="border-gray-300 rounded-md">
                    <option value="all">完食</option>
                    <option value="half">半分</option>
                    <option value="little">少し</option>
                    <option value="none">食べず</option>
                </select>
                <button class="md:col-span-5 bg-teal-700 text-white rounded-md py-2 text-sm font-semibold">ワンタップ記録</button>
            </form>
            @endif

            <div class="bg-white rounded shadow-sm divide-y">
                @forelse($logs as $log)
                    <div class="p-4 text-sm flex justify-between gap-3">
                        <div>
                            <div class="font-semibold">{{ $log->child->name ?? '-' }} / {{ $log->type }}</div>
                            <div class="text-gray-600">
                                @if($log->type==='diaper') {{ $log->diaper_status }}
                                @elseif($log->type==='milk') {{ $log->milk_ml }} ml
                                @else 食事: {{ $log->meal_amount }}
                                @endif
                                {{ $log->note }}
                            </div>
                        </div>
                        <div class="text-xs text-gray-400">{{ $log->logged_at?->format('H:i') }}</div>
                    </div>
                @empty
                    <div class="p-6 text-gray-500 text-sm">本日の記録はまだありません。</div>
                @endforelse
            </div>
            <div>{{ $logs->links() }}</div>
        </div>
    </div>
</x-app-layout>
