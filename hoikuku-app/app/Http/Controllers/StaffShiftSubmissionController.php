<?php

namespace App\Http\Controllers;

use App\Models\ShiftPeriod;
use App\Models\ShiftPattern;
use App\Models\ShiftSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\CarbonPeriod;


class StaffShiftSubmissionController extends Controller
{
    /**
     * シフト希望入力画面の表示
     */
    public function create(ShiftPeriod $shiftPeriod)
    {
        // 1. 提出期間のチェック（期間外なら一覧やエラー画面へリダイレクトなど）
        /*$now = now();
        if ($now->lt($shiftPeriod->start_date) || $now->gt($shiftPeriod->end_date)) {
            return redirect()->route('dashboard')->with('error', '現在はこの期間のシフト希望提出期間外です。');
        }
        */

        // 2. シフトパターン（早番・遅番など）の一覧を取得
        $shiftPatterns = ShiftPattern::all();

        // 3. 期間内の日付リストを生成（例: start_date から end_date まで）
        $periodDates = CarbonPeriod::create($shiftPeriod->start_date, $shiftPeriod->end_date);

        // 4. すでに提出済みのデータがあれば取得しておく（編集用）
        $user = Auth::user();
        $existingSubmissions = ShiftSubmission::where('user_id', $user->id)
            ->where('shift_period_id', $shiftPeriod->id)
            ->pluck('shift_pattern_id', 'target_date') // ['2026-04-01' => 1, ...] の形式
            ->toArray();

            return view('staff.shift_create', compact('shiftPeriod', 'shiftPatterns', 'periodDates', 'existingSubmissions'));
    }

    /**
     * シフト希望の保存・更新
     */
    public function store(Request $request, ShiftPeriod $shiftPeriod)
    {
        // 提出期間の再チェック
        /*$now = now();
        if ($now->lt($shiftPeriod->start_date) || $now->gt($shiftPeriod->end_date)) {
            return back()->with('error', '提出期間外のため保存できませんでした。');
        }
       */
      
        $user = Auth::user();
        $submissions = $request->input('submissions', []); // [ '2026-04-01' => '2', ... ]

        // 日付ごとに保存（すでに存在する場合は更新、値が空なら削除）
        foreach ($submissions as $date => $patternId) {
            if (!empty($patternId)) {
                ShiftSubmission::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'target_date' => $date,
                    ],
                    [
                        'shift_period_id' => $shiftPeriod->id,
                        'shift_pattern_id' => $patternId,
                    ]
                );
            } else {
                // 選択が空（未選択）に戻された場合はデータを削除
                ShiftSubmission::where('user_id', $user->id)
                    ->where('target_date', $date)
                    ->delete();
            }
        }

        return redirect()->route('staff.shifts.create', $shiftPeriod->id)
            ->with('success', 'シフト希望を保存しました。');
    }
}