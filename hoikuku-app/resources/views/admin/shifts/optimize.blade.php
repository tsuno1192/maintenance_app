<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('シフト自動割り当て実行') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if ($errors->any())
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-600 p-4 rounded-lg text-sm">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <p class="text-gray-600 mb-6 text-sm">
                        スタッフからのシフト希望、資格要件（必要スキル人数）、および労働基準法（連続勤務制限など）を考慮して、指定期間のシフト案を自動生成します。
                    </p>

                    <!-- 自動割り当て実行フォーム -->
                    <form action="{{ route('admin.shifts.optimize.store') }}" method="POST" class="space-y-4 max-w-lg">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">対象開始日</label>
                            <input type="date" name="start_date" value="2026-09-01" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">対象終了日</label>
                            <input type="date" name="end_date" value="2026-09-07" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded shadow text-sm">
                                シフト自動割り当てを実行する
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>