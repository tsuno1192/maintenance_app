<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            シフト希望提出: {{ $shiftPeriod->name ?? '期間シフト' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('staff.shifts.store', $shiftPeriod->id) }}" method="POST">
                        @csrf

                        @php
                            // CarbonPeriodを配列に変換、または最初の1件を取得
                            $periodArray = iterator_to_array($periodDates);
                            $firstDate = count($periodArray) > 0 ? \Carbon\Carbon::parse($periodArray[0]) : now();
                            
                            $datesMap = [];
                            foreach($periodDates as $date) {
                                $dStr = is_string($date) ? $date : $date->format('Y-m-d');
                                $datesMap[$dStr] = true;
                            }

                            $startOfMonth = $firstDate->copy()->startOfMonth();
                            $endOfMonth = $firstDate->copy()->endOfMonth();

                            $calendarStart = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                            $calendarEnd = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
                        @endphp

                        <div class="mb-4 text-lg font-bold text-center">
                            {{ $firstDate->format('Y年n月') }}
                        </div>

                        <div class="grid grid-cols-7 gap-1 bg-gray-200 p-1 rounded-lg">
                            <!-- 曜日ヘッダー -->
                            <div class="text-center font-bold text-red-600 bg-gray-50 py-2">日</div>
                            <div class="text-center font-bold text-gray-700 bg-gray-50 py-2">月</div>
                            <div class="text-center font-bold text-gray-700 bg-gray-50 py-2">火</div>
                            <div class="text-center font-bold text-gray-700 bg-gray-50 py-2">水</div>
                            <div class="text-center font-bold text-gray-700 bg-gray-50 py-2">木</div>
                            <div class="text-center font-bold text-gray-700 bg-gray-50 py-2">金</div>
                            <div class="text-center font-bold text-blue-600 bg-gray-50 py-2">土</div>

                            <!-- カレンダーの日付マス -->
                            @php
                                $currentDay = $calendarStart->copy();
                            @endphp

                            @while($currentDay <= $calendarEnd)
                                @php
                                    $dStr = $currentDay->format('Y-m-d');
                                    $isTargetMonth = $currentDay->month === $firstDate->month;
                                    $isTargetPeriod = isset($datesMap[$dStr]);
                                    $selectedPatternId = $existingSubmissions[$dStr] ?? '';
                                @endphp

                                <div class="bg-white min-h-[110px] p-2 flex flex-col justify-between {{ $isTargetMonth ? '' : 'bg-gray-50 opacity-40' }}">
                                    <div>
                                        <span class="text-sm font-semibold {{ $currentDay->dayOfWeek === 0 ? 'text-red-600' : ($currentDay->dayOfWeek === 6 ? 'text-blue-600' : 'text-gray-700') }}">
                                            {{ $currentDay->format('j') }}
                                        </span>
                                    </div>

                                    @if($isTargetPeriod)
                                        <div class="mt-1">
                                            <select name="shifts[{{ $dStr }}]" class="w-full text-xs rounded border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 p-1">
                                                <option value="">未選択</option>
                                                @foreach($shiftPatterns as $pattern)
                                                    <option value="{{ $pattern->id }}" {{ $selectedPatternId == $pattern->id ? 'selected' : '' }}>
                                                        {{ $pattern->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                </div>

                                @php
                                    $currentDay->addDay();
                                @endphp
                            @endwhile
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700 shadow">
                                シフト希望を保存する
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>