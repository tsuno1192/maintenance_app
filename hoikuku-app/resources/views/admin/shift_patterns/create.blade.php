<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            シフトパターン新規作成
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form action="{{ route('admin.shift_patterns.store') }}" method="POST">
                        @csrf

                        {{-- パターン名 --}}
                        <div class="mb-4">
                            <label for="name" class="block font-medium text-sm text-gray-700">シフトパターン名</label>
                            <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror" id="name" name="name" required>
                                <option value="">選択してください</option>
                                <option value="早番" {{ old('name') == '早番' ? 'selected' : '' }}>早番</option>
                                <option value="遅番" {{ old('name') == '遅番' ? 'selected' : '' }}>遅番</option>
                                <option value="日勤" {{ old('name') == '日勤' ? 'selected' : '' }}>日勤</option>
                                <option value="平日夜勤" {{ old('name') == '夜勤' ? 'selected' : '' }}>夜勤</option>
                                <option value="休日早番" {{ old('name') == '夜勤' ? 'selected' : '' }}>夜勤</option>
                                <option value="休日遅番" {{ old('name') == '夜勤' ? 'selected' : '' }}>夜勤</option>
                                <option value="休日日勤" {{ old('name') == '夜勤' ? 'selected' : '' }}>夜勤</option>
                            </select>
                            @error('name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- 開始時間 --}}
                        <div class="mb-4">
                            <label for="start_time" class="block font-medium text-sm text-gray-700">開始時間</label>
                            <input type="time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('start_time') border-red-500 @enderror" id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                            @error('start_time')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- 終了時間 --}}
                        <div class="mb-4">
                            <label for="end_time" class="block font-medium text-sm text-gray-700">終了時間</label>
                            <input type="time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('end_time') border-red-500 @enderror" id="end_time" name="end_time" value="{{ old('end_time') }}" required>
                            @error('end_time')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- 必要スキルと人数設定 --}}
                        <div class="mb-6">
                            <label class="block font-medium text-sm text-gray-700 mb-2">必要資格と人数設定</label>
                            <div class="bg-gray-50 p-4 rounded-md border border-gray-200 space-y-3">
                                @foreach($skills as $index => $skill)
                                <div class="flex items-center justify-between bg-white p-3 rounded shadow-sm border border-gray-100">
                                    {{-- チェックボックス --}}
                                    <div class="flex items-center">
                                        <input class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" type="checkbox"
                                            name="skills[{{ $index }}][id]"
                                            value="{{ $skill->id }}"
                                            id="skill_{{ $skill->id }}"
                                            {{ old("skills.$index.id") == $skill->id ? 'checked' : '' }}>
                                        <label class="ml-2 font-medium text-gray-700" for="skill_{{ $skill->id }}">
                                            {{ $skill->name }}
                                        </label>
                                    </div>
                                    {{-- 必要人数入力欄 --}}
                                    <div class="flex items-center space-x-2">
                                        <input type="number" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                            name="skills[{{ $index }}][required_count]"
                                            value="{{ old("skills.$index.required_count", 1) }}"
                                            min="1" placeholder="人数">
                                        <span class="text-sm text-gray-600">人</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @error('skills')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- ボタン類 --}}
                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('admin.shift_patterns.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                キャンセル
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                登録する
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>