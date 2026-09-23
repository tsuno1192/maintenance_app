<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">登園・検温・お迎え</h2></x-slot>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))<div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>@endif

            <form method="GET" class="bg-white p-4 rounded shadow-sm flex gap-3 items-end">
                <div><label class="text-xs">日付</label><input type="date" name="date" value="{{ $date }}" class="border-gray-300 rounded-md"></div>
                <button class="bg-gray-800 text-white px-3 py-2 rounded text-sm">表示</button>
            </form>

            <form method="POST" action="{{ route('attendance.upsert') }}" class="bg-white shadow-sm sm:rounded-lg p-6 grid md:grid-cols-3 gap-3">
                @csrf
                <select name="child_id" required class="border-gray-300 rounded-md">
                    <option value="">児童</option>
                    @foreach($children as $child)<option value="{{ $child->id }}">{{ $child->name }}</option>@endforeach
                </select>
                <input type="date" name="attendance_date" value="{{ $date }}" required class="border-gray-300 rounded-md">
                <select name="status" class="border-gray-300 rounded-md">
                    <option value="attending">登園予定</option>
                    <option value="late">遅刻</option>
                    <option value="absent">お休み</option>
                </select>
                <input type="number" step="0.1" name="temperature" placeholder="体温℃" class="border-gray-300 rounded-md">
                <input type="time" name="pickup_eta" class="border-gray-300 rounded-md">
                <input type="text" name="pickup_note" placeholder="お迎えメモ" class="border-gray-300 rounded-md">
                <button class="md:col-span-3 bg-indigo-600 text-white rounded-md py-2 text-sm font-semibold">送信・同期</button>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left"><tr><th class="p-3">児童</th><th class="p-3">状態</th><th class="p-3">体温</th><th class="p-3">お迎え</th></tr></thead>
                    <tbody>
                    @forelse($attendances as $row)
                        <tr class="border-t">
                            <td class="p-3">{{ $row->child->name ?? '-' }}</td>
                            <td class="p-3">{{ $row->status }}</td>
                            <td class="p-3">{{ $row->temperature ? $row->temperature.'℃' : '-' }}</td>
                            <td class="p-3">{{ $row->pickup_eta ?? '-' }} {{ $row->pickup_note }}</td>
                        </tr>
                    @empty
                        <tr><td class="p-4 text-gray-500" colspan="4">データがありません。</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $attendances->links() }}</div>
        </div>
    </div>
</x-app-layout>
