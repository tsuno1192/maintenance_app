<?php

namespace Database\Factories;

use App\Enums\RepairReportStatus;
use App\Models\RepairReport;
use App\Models\Trouble;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RepairReport>
 */
class RepairReportFactory extends Factory
{
    protected $model = RepairReport::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trouble_id' => Trouble::factory(),
            'category_major' => '設備',
            'category_middle' => '反応槽A',
            'category_minor' => '軸受',
            'title' => fake()->sentence(3),
            'content' => fake()->paragraph(),
            'investigation_result' => fake()->sentence(),
            'cause' => fake()->sentence(),
            'repaired_on' => now()->toDateString(),
            'used_spare_parts' => '軸受 1個',
            'used_drawings' => 'DWG-001',
            'trial_run_result' => '異常なし',
            'operation_records' => '振動正常',
            'status' => RepairReportStatus::Leader,
            'maintenance_staff_approved' => true,
            'maintenance_staff_approved_at' => now(),
        ];
    }

    public function status(RepairReportStatus $status): static
    {
        return $this->state(function () use ($status) {
            $data = ['status' => $status];

            foreach (RepairReportStatus::workflowSteps() as $step) {
                if ($step === RepairReportStatus::Completed) {
                    continue;
                }
                $flag = $step->approvalFlagColumn();
                $at = $step->approvalAtColumn();
                $approved = $status->isAfter($step);
                if ($flag) {
                    $data[$flag] = $approved;
                }
                if ($at) {
                    $data[$at] = $approved ? now() : null;
                }
            }

            if ($status === RepairReportStatus::Completed) {
                foreach (RepairReportStatus::workflowSteps() as $step) {
                    $flag = $step->approvalFlagColumn();
                    $at = $step->approvalAtColumn();
                    if ($flag) {
                        $data[$flag] = true;
                    }
                    if ($at) {
                        $data[$at] = now();
                    }
                }
            }

            return $data;
        });
    }
}
