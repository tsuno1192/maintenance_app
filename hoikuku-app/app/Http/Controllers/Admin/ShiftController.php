<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\User;
use App\Models\ShiftPattern;

class ShiftController extends Controller
{
    /**
     * シフト登録画面の表示
     */
    public function create()
    {
        // 登録に必要なスタッフ一覧とシフトパターン一覧を取得
        $staffs = User::all(); // 必要に応じてスタッフ権限などで絞り込み
        $shiftPatterns = ShiftPattern::all();

        return view('admin.shifts.create', compact('staffs', 'shiftPatterns'));
    }

    /**
     * シフトの保存処理
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'staff_id' => ['required', 'exists:users,id'],
            'target_date' => ['required', 'date'],
            'shift_pattern_id' => ['required', 'exists:shift_patterns,id'],
            'status' => ['nullable', 'integer'],
        ]);

        // 選択されたシフトパターンから時間などを自動取得して保存する場合
        $shiftPattern = ShiftPattern::findOrFail($validated['shift_pattern_id']);

        Shift::create([
            'staff_id' => $validated['staff_id'],
            'target_date' => $validated['target_date'],
            'shift_pattern_id' => $validated['shift_pattern_id'],
            'start_time' => $shiftPattern->start_time,
            'end_time' => $shiftPattern->end_time,
            'status' => $validated['status'] ?? 0, // 0: 仮決定 or 未確定 など
        ]);

        return redirect()->route('admin.shifts.create')
            ->with('success', 'シフトを登録しました。');
    }
}