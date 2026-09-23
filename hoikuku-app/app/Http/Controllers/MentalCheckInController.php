<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MentalCheckIn;
use App\Models\AnonymousChat;
use App\Models\AnonymousMessage;
use App\Models\ThanksCard;

class MentalCheckInController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'weather' => 'required|string|in:sunny,cloudy,rainy,thunderstorm',
            'sleep_hours' => 'required|integer|min:0|max:24',
            'relation_stress' => 'required|integer|min:1|max:5',
            'physical_fatigue' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['checked_at'] = now()->toDateString();

        // 1日1回までの制限を入れる場合などのバリデーションをここに記述
        MentalCheckIn::updateOrCreate(
            ['user_id' => $validated['user_id'], 'checked_at' => $validated['checked_at']],
            $validated
        );

        return response()->json(['message' => '今日の心の天気を記録しました。今日も無理せずいきましょう！'], 201);
    }

    // 園全体の匿名集計データ（管理者ダッシュボード用）
    public function nurseryDashboardSummary(Request $request)
    {
        // 個人を特定できないよう、平均値や集計数のみを返す
        $today = now()->toDateString();
        
        $summary = MentalCheckIn::where('checked_at', $today)
            ->selectRaw('weather, count(*) as count')
            ->groupBy('weather')
            ->get();

        $avgStress = MentalCheckIn::where('checked_at', $today)->avg('relation_stress');
        $avgFatigue = MentalCheckIn::where('checked_at', $today)->avg('physical_fatigue');

        return response()->json([
            'date' => $today,
            'weather_distribution' => $summary,
            'average_relation_stress' => round($avgStress, 1),
            'average_physical_fatigue' => round($avgFatigue, 1),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
