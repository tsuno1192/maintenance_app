<?php

namespace App\Services;

use App\Models\Shift;
use App\Models\ShiftRequest;
use App\Models\ShiftPattern;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ShiftOptimizerService
{
    public function optimize(string $startDate, string $endDate, array $staffIds)
    {
        $shiftRequests = ShiftRequest::whereBetween('target_date', [$startDate, $endDate])
            ->whereIn('staff_id', $staffIds)
            ->get()
            ->groupBy(['target_date', 'staff_id']);

        $patterns = ShiftPattern::with('requiredSkills')->get()->keyBy('id');
        $period = Carbon::parse($startDate)->toPeriod($endDate);

        $staffWorkCounts = array_fill_keys($staffIds, 0);
        
        // 【追加】スタッフごとの連続勤務日数を追跡するトラッカー
        $consecutiveWorkDays = array_fill_keys($staffIds, 0);

        $staffSkillsMap = User::whereIn('id', $staffIds)->with('skills')->get()->keyBy('id')->map(function ($staff) {
            return $staff->skills->pluck('id')->toArray();
        });

        DB::beginTransaction();
        try {
            foreach ($period as $date) {
                $dateStr = $date->toDateString();
                $dailyAssignments = [];

                foreach ($staffIds as $staffId) {
                    $request = $shiftRequests[$dateStr][$staffId][0] ?? null;

                    // 1. ハード制約: 休日希望ならスキップ（出勤日数をリセット）
                    if ($request && $request->request_type === 0) {
                        $consecutiveWorkDays[$staffId] = 0;
                        continue; 
                    }

                    // 【追加】ハード制約: 連続勤務日数が上限（例: 6日）に達している場合、強制的に休日扱いにする
                    if ($consecutiveWorkDays[$staffId] >= 6) {
                        $consecutiveWorkDays[$staffId] = 0;
                        continue; // 休日として扱う（アサインしない）
                    }

                    $assignedPatternId = null;
                    $startTime = null;
                    $endTime = null;

                    if ($request) {
                        if ($request->request_type === 1) {
                            $assignedPatternId = $request->shift_pattern_id;
                        } elseif ($request->request_type === 2) {
                            $startTime = $request->start_time;
                            $endTime = $request->end_time;
                        }
                    } else {
                        // 公平性を考慮して最も労働が少ないスタッフを選択
                        $assignedPatternId = $this->selectStaffWithLeastWork($staffWorkCounts, $staffIds);
                    }

                    // 割り当て候補を保持
                    $dailyAssignments[$staffId] = [
                        'shift_pattern_id' => $assignedPatternId,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                    ];
                }

                // 2. 動的スキル要件チェックと補正
                $dailyAssignments = $this->ensureDynamicSkillRequirements(
                    $dailyAssignments, 
                    $patterns, 
                    $staffSkillsMap, 
                    $staffIds
                );

                // 3. データベース保存とトラッカー（連続勤務・労働回数）の更新
                foreach ($staffIds as $staffId) {
                    $assignment = $dailyAssignments[$staffId] ?? null;

                    if ($assignment && ($assignment['shift_pattern_id'] || $assignment['start_time'])) {
                        // 出勤する場合
                        Shift::updateOrCreate(
                            ['staff_id' => $staffId, 'target_date' => $dateStr],
                            [
                                'shift_pattern_id' => $assignment['shift_pattern_id'],
                                'start_time' => $assignment['start_time'],
                                'end_time' => $assignment['end_time'],
                                'status' => 0,
                            ]
                        );
                        $staffWorkCounts[$staffId]++;
                        $consecutiveWorkDays[$staffId]++; // 連続勤務日数を加算
                    } else {
                        // 休日（アサインなし）の場合、もし既存データがあれば削除または休日ステータスにする等の処理
                        // ここではシンプルに連続勤務カウントをリセット
                        $consecutiveWorkDays[$staffId] = 0;
                        
                        // 休日としてレコードをクリア、または保存
                        Shift::where('staff_id', $staffId)->where('target_date', $dateStr)->delete();
                    }
                }
            }

            DB::commit();
            return ['status' => true, 'message' => '労基法制約（連続勤務上限）およびスキル要件を考慮したシフト自動割り当て案を作成しました。'];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function ensureDynamicSkillRequirements(array $assignments, $patterns, $staffSkillsMap, array $allStaffIds): array
    {
        foreach ($patterns as $patternId => $pattern) {
            $requiredSkills = $pattern->requiredSkills;
            if ($requiredSkills->isEmpty()) continue;

            foreach ($requiredSkills as $skill) {
                $requiredCount = $skill->pivot->required_count;
                $skillId = $skill->id;

                $currentCount = 0;
                foreach ($assignments as $staffId => $assignment) {
                    if (($assignment['shift_pattern_id'] ?? null) === $patternId) {
                        if (in_array($skillId, $staffSkillsMap[$staffId] ?? [])) {
                            $currentCount++;
                        }
                    }
                }

                if ($currentCount < $requiredCount) {
                    $shortage = $requiredCount - $currentCount;

                    foreach ($allStaffIds as $staffId) {
                        if ($shortage <= 0) break;
                        if (isset($assignments[$staffId])) continue;

                        $staffSkills = $staffSkillsMap[$staffId] ?? [];
                        if (in_array($skillId, $staffSkills)) {
                            $assignments[$staffId] = [
                                'shift_pattern_id' => $patternId,
                                'start_time' => null,
                                'end_time' => null,
                            ];
                            $shortage--;
                        }
                    }
                }
            }
        }
        return $assignments;
    }

    private function selectStaffWithLeastWork(array $staffWorkCounts, array $staffIds): int
    {
        $filteredCounts = array_intersect_key($staffWorkCounts, array_flip($staffIds));
        asort($filteredCounts);
        return (int) array_key_first($filteredCounts) ?? $staffIds[0];
    }
}