<?php

namespace App\Http\Controllers;

use App\Models\ShiftRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftRequestController extends Controller
{
    /**
     * シフト希望を登録・更新する
     */
    public function store(Request $request): JsonResponse
    {
        // 1. バリデーション
        $validated = $request->validate([
            'staff_id' => ['required', 'integer'],
            'target_date' => ['required', 'date'],
            'request_type' => ['required', 'integer', 'in:0,1,2'], // 0:休日, 1:固定, 2:自由
            
            // request_typeが 1 (固定) の場合のみ shift_pattern_id が必須
            'shift_pattern_id' => [
                'nullable',
                'required_if:request_type,1',
                'exists:shift_patterns,id'
            ],
            
            // request_typeが 2 (自由) の場合のみ start_time / end_time が必須
            'start_time' => [
                'nullable',
                'required_if:request_type,2',
                'date_format:H:i',
                'before:end_time'
            ],
            'end_time' => [
                'nullable',
                'required_if:request_type,2',
                'date_format:H:i',
                'after:start_time'
            ],
            'memo' => ['nullable', 'string', 'max:500'],
        ]);

        // 2. データの登録（同一スタッフ・同日の希望がすでにあれば上書き保存するupdateOrCreateを活用）
        $shiftRequest = ShiftRequest::updateOrCreate(
            [
                'staff_id' => $validated['staff_id'],
                'target_date' => $validated['target_date'],
            ],
            [
                'request_type' => $validated['request_type'],
                // 区分に応じて不要なデータはnullにする
                'shift_pattern_id' => $validated['request_type'] == 1 ? $validated['shift_pattern_id'] : null,
                'start_time' => $validated['request_type'] == 2 ? $validated['start_time'] : null,
                'end_time' => $validated['request_type'] == 2 ? $validated['end_time'] : null,
                'memo' => $validated['memo'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'シフト希望を正常に保存しました。',
            'data' => $shiftRequest->load('shiftPattern'),
        ], 201);
    }
}
