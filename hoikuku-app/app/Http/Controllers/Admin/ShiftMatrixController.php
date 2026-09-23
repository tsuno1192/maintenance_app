<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\ShiftPattern;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftMatrixController extends Controller
{
    /**
     * マトリクス表示用のシフト一覧データを取得する
     */
    public function index(Request $request): JsonResponse
    {
        // バリデーション（期間の指定が必須）
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        // 1. 対象期間の全日付リストを生成（横軸用）
        $period = Carbon::parse($startDate)->toPeriod($endDate);
        $dates = [];
        foreach ($period as $date) {
            $dates[] = $date->toDateString();
        }

        // 2. スタッフ一覧を取得（スキルや所属情報も一緒にEager Loading）
        $staffs = User::query()
            ->whereIn('role', ['staff', 'admin'])
            ->with([
                'skills',
                'shifts' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('target_date', [$startDate, $endDate]);
                },
            ])
            ->orderBy('name')
            ->get();

        // 3. 選択肢として使うシフトパターン一覧を取得
        $shiftPatterns = ShiftPattern::with('requiredSkills')->get();

        return response()->json([
            'status' => true,
            'data' => [
                'dates' => $dates,                  // 期間内の日付配列
                'staffs' => $staffs,                // スタッフ一覧（紐付くシフトデータ含む）
                'shift_patterns' => $shiftPatterns, // 変更用プルダウン等で使うパターン一覧
            ]
        ], 200);
    }
}