<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ShiftOptimizerService;
use App\Models\User;
use Illuminate\Http\Request;

class ShiftOptimizationController extends Controller
{
    protected ShiftOptimizerService $optimizerService;

    public function __construct(ShiftOptimizerService $optimizerService)
    {
        $this->optimizerService = $optimizerService;
    }

    /**
     * 自動割り当て実行画面の表示
     */
    public function create()
    {
        return view('admin.shifts.optimize');
    }

    /**
     * 自動割り当てロジックの実行
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        // 対象となる全スタッフのIDを取得
        $staffIds = User::pluck('id')->toArray();

        try {
            // サービスクラスの実行
            $result = $this->optimizerService->optimize(
                $validated['start_date'],
                $validated['end_date'],
                $staffIds
            );

            return redirect()->route('admin.shifts.matrix')
                ->with('success', $result['message']);

        } catch (\Exception $e) {
            report($e);

            return back()->withErrors(['error' => '自動割り当て中にエラーが発生しました。']);
        }
    }
}