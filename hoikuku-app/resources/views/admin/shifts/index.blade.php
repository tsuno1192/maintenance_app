<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('シフト管理ダッシュボード（マトリクス）') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <!-- フィルター・操作エリア -->
                    <div class="mb-6 flex flex-wrap items-center gap-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">開始日</label>
                            <input type="date" id="start_date" value="2026-09-01" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">終了日</label>
                            <input type="date" id="end_date" value="2026-09-07" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                        </div>
                        <div class="self-end">
                            <button onclick="fetchShiftMatrix()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow text-sm">
                                表示を更新
                            </button>
                        </div>
                    </div>

                    <!-- マトリクス表を表示するコンテナ -->
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 text-sm" id="shift-matrix-table">
                            <thead class="bg-gray-100" id="matrix-head">
                                <!-- 動的に日付ヘッダーが挿入されます -->
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 sticky left-0 bg-gray-100 z-10">スタッフ名</th>
                                    <th class="px-4 py-3 text-center text-gray-500">データを読み込んでください...</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="matrix-body">
                                <!-- 動的にスタッフ行とシフトセルが挿入されます -->
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- 簡易スクリプト（Ajaxでデータを取得してテーブルを構築する例） -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        async function fetchShiftMatrix() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const theadTr = document.querySelector('#matrix-head tr');
            const tbody = document.getElementById('matrix-body');

            tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-gray-500">読み込み中...</td></tr>`;

            try {
                const response = await axios.get('/api/admin/shifts/matrix', {
                    params: { start_date: startDate, end_date: endDate }
                });
                const data = response.data.data;

                // 1. ヘッダーの構築（スタッフ名 + 日付列）
                let headHtml = `<th class="px-4 py-3 text-left font-semibold text-gray-700 sticky left-0 bg-gray-100 z-10 min-w-[180px]">スタッフ名</th>`;
                data.dates.forEach(dateStr => {
                    const dateObj = new Date(dateStr);
                    const m = dateObj.getMonth() + 1;
                    const d = dateObj.getDate();
                    const days = ['日', '月', '火', '水', '木', '金', '土'];
                    const w = days[dateObj.getDay()];
                    headHtml += `<th class="px-3 py-3 text-center font-medium text-gray-600 min-w-[90px]">${m}/${d}(${w})</th>`;
                });
                theadTr.innerHTML = headHtml;

                // 2. ボディの構築（スタッフ行 × 日付セル）
                let bodyHtml = '';
                data.staffs.forEach(staff => {
                    bodyHtml += `<tr>`;
                    // スタッフ名＆スキルバッジ
                    let skillsHtml = staff.skills.map(s => `<span class="bg-indigo-100 text-indigo-800 text-xs px-1.5 py-0.5 rounded mr-1">${s.name}</span>`).join('');
                    bodyHtml += `<td class="px-4 py-3 font-medium text-gray-900 sticky left-0 bg-white z-10 border-r">
                        <div>${staff.name}</div>
                        <div class="mt-1">${skillsHtml}</div>
                    </td>`;

                    // 各日付のシフト
                    data.dates.forEach(dateStr => {
                        const shift = staff.shifts.find(s => s.target_date === dateStr);
                        let cellText = '-';
                        let cellClass = 'text-gray-400';

                        if (shift) {
                            if (shift.shift_pattern_id) {
                                cellText = `パターン:${shift.shift_pattern_id}`;
                                cellClass = 'bg-blue-50 text-blue-700 font-semibold';
                            } else if (shift.start_time) {
                                cellText = `${shift.start_time.slice(0,5)}-${shift.end_time.slice(0,5)}`;
                                cellClass = 'bg-green-50 text-green-700 font-semibold';
                            }
                        }

                        bodyHtml += `<td class="px-2 py-3 text-center text-xs cursor-pointer hover:bg-gray-50 border-r ${cellClass}" onclick="openCellModal(${staff.id}, '${dateStr}')">
                            ${cellText}
                        </td>`;
                    });
                    bodyHtml += `</tr>`;
                });

                tbody.innerHTML = bodyHtml;

            } catch (error) {
                console.error(error);
                tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-red-500">データの取得に失敗しました。</td></tr>`;
            }
        }

        function openCellModal(staffId, dateStr) {
            alert(`スタッフID: ${staffId} の ${dateStr} のセルがクリックされました（モーダルを開く処理へ接続）`);
        }

        // 初回ロード時に実行
        document.addEventListener('DOMContentLoaded', fetchShiftMatrix);
    </script>
    @endpush
</x-app-layout>