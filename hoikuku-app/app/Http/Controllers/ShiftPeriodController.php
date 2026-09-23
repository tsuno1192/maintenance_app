<?php

namespace App\Http\Controllers;

use App\Models\ShiftPeriod;
use Illuminate\Http\Request;

class ShiftPeriodController extends Controller
{
    public function index()
    {
        // 提出期間データを取得（作成日が新しい順に表示）
        $periods = \App\Models\ShiftPeriod::orderBy('created_at', 'desc')->get();

        // フォルダ直下のファイル名を指定
        return view('admin.shifts.periods_index', compact('periods'));
    }

    // 登録画面の表示
    public function create()
    {
        return view('admin.shifts.periods_create'); // 修正：ファイル名を指定
    }

    // データの保存処理
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        ShiftPeriod::create([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->route('dashboard')->with('success', 'シフト提出期間を作成しました。');
    }

    // シフト提出期間のデータをJSONで返す
    public function getPeriodsJson()
    {
        $periods = \App\Models\ShiftPeriod::all();

        // FullCalendar用のフォーマット（title, start, end）に変換
        $events = $periods->map(function ($period) {
            return [
                'title' => $period->name,
                'start' => $period->start_date,
                'end' => \Carbon\Carbon::parse($period->end_date)->addDay()->format('Y-m-d'), // FullCalendarの仕様で終了日の翌日まで指定すると期間全体が綺麗に塗られます
            ];
        });

        return response()->json($events);
    }
}
