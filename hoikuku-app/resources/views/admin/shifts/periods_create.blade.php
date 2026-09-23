<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            シフト提出期間の作成
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form action="{{ route('shifts.periods.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">期間名（例: 4月度シフト）</label>
                        <input type="text" name="name" class="w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">開始日</label>
                        <input type="date" name="start_date" class="w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">終了日</label>
                        <input type="date" name="end_date" class="w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>

                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-md hover:bg-blue-700">
                        作成する
                    </button>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>