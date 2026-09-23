<?php

namespace App\Services;

use App\Events\NapAlertCreated;
use App\Models\NapAlert;
use App\Models\NapCheck;

class NapMonitoringService
{
    /**
     * @param  array{
     *   child_id:int,
     *   posture?:string,
     *   breathing_status?:string,
     *   sensor_source?:string|null,
     *   notes?:string|null,
     *   user_id?:int|null,
     *   checked_at?:string|null
     * }  $payload
     */
    public function record(array $payload): NapCheck
    {
        $posture = $payload['posture'] ?? 'unknown';
        $breathing = $payload['breathing_status'] ?? 'normal';
        $alertLevel = $this->resolveAlertLevel($posture, $breathing);

        $check = NapCheck::create([
            'child_id' => $payload['child_id'],
            'user_id' => $payload['user_id'] ?? null,
            'checked_at' => $payload['checked_at'] ?? now(),
            'posture' => $posture,
            'breathing_status' => $breathing,
            'sensor_source' => $payload['sensor_source'] ?? null,
            'alert_level' => $alertLevel,
            'notes' => $payload['notes'] ?? null,
        ]);

        if (in_array($alertLevel, ['warning', 'critical'], true)) {
            $alert = NapAlert::create([
                'nap_check_id' => $check->id,
                'child_id' => $check->child_id,
                'message' => $this->buildAlertMessage($check),
            ]);

            event(new NapAlertCreated($alert));
        }

        return $check->load('alert');
    }

    private function resolveAlertLevel(string $posture, string $breathing): string
    {
        if ($breathing === 'none') {
            return 'critical';
        }

        if ($breathing === 'irregular' || $posture === 'stomach') {
            return 'warning';
        }

        return 'none';
    }

    private function buildAlertMessage(NapCheck $check): string
    {
        return sprintf(
            '午睡アラート: 児童ID %d / 体位=%s / 呼吸=%s',
            $check->child_id,
            $check->posture,
            $check->breathing_status
        );
    }
}
