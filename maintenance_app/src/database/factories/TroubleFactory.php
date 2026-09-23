<?php

namespace Database\Factories;

use App\Enums\TroubleStatus;
use App\Models\Trouble;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trouble>
 */
class TroubleFactory extends Factory
{
    protected $model = Trouble::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_major' => '設備',
            'category_middle' => '反応槽A',
            'category_minor' => '軸受',
            'title' => fake()->sentence(3),
            'content' => fake()->paragraph(),
            'investigation' => fake()->sentence(),
            'estimated_cause' => fake()->sentence(),
            'occurred_on' => now()->toDateString(),
            'repair_requested_on' => now()->addDay()->toDateString(),
            'required_spare_parts' => '軸受 1個',
            'required_drawings' => 'DWG-001',
            'created_group' => '運転Gr',
            'reporter_name' => fake()->name(),
            'reporter_user_id' => null,
            'status' => TroubleStatus::Leader,
            'discoverer_approved' => true,
            'discoverer_approved_at' => now(),
        ];
    }

    public function forGroup(string $group): static
    {
        return $this->state(fn () => ['created_group' => $group]);
    }

    public function status(TroubleStatus $status): static
    {
        return $this->state(function () use ($status) {
            $data = ['status' => $status];

            foreach (TroubleStatus::workflowSteps() as $step) {
                if ($step === TroubleStatus::Completed) {
                    continue;
                }
                $flag = $step->approvalFlagColumn();
                $at = $step->approvalAtColumn();
                $approved = $status->isAfter($step) || ($status === TroubleStatus::Completed);
                // Current step not yet approved
                if ($status === $step) {
                    $approved = false;
                }
                // Steps before current are approved
                if ($status->isAfter($step)) {
                    $approved = true;
                }
                if ($flag) {
                    $data[$flag] = $approved;
                }
                if ($at) {
                    $data[$at] = $approved ? now() : null;
                }
            }

            if ($status === TroubleStatus::Completed) {
                foreach (TroubleStatus::workflowSteps() as $step) {
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

    public function withReporter(?User $user = null): static
    {
        return $this->state(function () use ($user) {
            $user ??= User::factory()->create();

            return [
                'reporter_user_id' => $user->id,
                'reporter_name' => $user->name,
            ];
        });
    }
}
