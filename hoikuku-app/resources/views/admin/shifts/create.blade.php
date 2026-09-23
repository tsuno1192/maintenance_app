<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            スタッフシフト登録
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('admin.shifts.store') }}" method="POST">
                    @csrf

                    {{-- 対象スタッフ選択 --}}
                    <div class="mb-4">
                        <label for="staff_id" class="block font-medium text-sm text-gray-700">スタッフ名</label>
                        <select name="staff_id" id="staff_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">スタッフを選択してください</option>
                            @foreach($staffs as $staff)
                                <option value="{{ $staff->id }}" {{ old('staff_id') == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('staff_id')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 対象日付選択 --}}
                    <div class="mb-4">
                        <label for="target_date" class="block font-medium text-sm text-gray-700">対象日付</label>
                        <input type="date" name="target_date" id="target_date" value="{{ old('target_date', date('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        @error('target_date')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- シフトパターン選択 --}}
                    <div class="mb-4">
                        <label for="shift_pattern_id" class="block font-medium text-sm text-gray-700">シフトパターン（時間）</label>
                        <select name="shift_pattern_id" id="shift_pattern_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">シフトパターンを選択してください</option>
                            @foreach($shiftPatterns as $pattern)
                                <option value="{{ $pattern->id }}" {{ old('shift_pattern_id') == $pattern->id ? 'selected' : '' }}>
                                    {{ $pattern->name }} （{{ $pattern->start_time }} 〜 {{ $pattern->end_time }}）
                                </option>
                            @endforeach
                        </select>
                        @error('shift_pattern_id')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            登録する
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>