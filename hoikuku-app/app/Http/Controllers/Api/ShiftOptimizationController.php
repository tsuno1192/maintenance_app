<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ShiftOptimizerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftOptimizationController extends Controller
{
    protected ShiftOptimizerService $optimizerService;

    public function __construct(ShiftOptimizerService $optimizerService)
    {
        $this->optimizerService = $optimizerService;
    }

    /**
     * シフトの自動割り当てを実行する（スタッフ・管理者のみ）
     */
    public function generate(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isStaff(), 403);

        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'staff_ids' => ['required', 'array'],
            'staff_ids.*' => ['integer', 'exists:users,id'],
        ]);

        try {
            $result = $this->optimizerService->optimize(
                $validated['start_date'],
                $validated['end_date'],
                $validated['staff_ids']
            );

            return response()->json([
                'message' => 'シフトの自動割り当て案を作成しました。',
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'message' => 'シフトの自動生成に失敗しました。',
            ], 500);
        }
    }
}
