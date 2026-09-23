<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NapMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NapSensorController extends Controller
{
    public function store(Request $request, NapMonitoringService $monitor): JsonResponse
    {
        $validated = $request->validate([
            'child_id' => ['required', 'exists:children,id'],
            'posture' => ['required', 'in:back,side,stomach,unknown'],
            'breathing_status' => ['required', 'in:normal,irregular,none'],
            'sensor_source' => ['nullable', 'string', 'max:64'],
            'checked_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $check = $monitor->record($validated);

        return response()->json([
            'status' => 'success',
            'alert_level' => $check->alert_level,
            'data' => $check,
        ], 201);
    }
}
