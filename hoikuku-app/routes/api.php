<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ShiftOptimizationController;
use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\NapSensorController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'role:staff,admin'])
    ->post('/shifts/optimize', [ShiftOptimizationController::class, 'generate']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/consultations', [ConsultationController::class, 'index']);
    Route::post('/consultations', [ConsultationController::class, 'store']);
    Route::post('/consultations/{id}/messages', [ConsultationController::class, 'sendMessage']);
    Route::patch('/consultations/{id}/status', [ConsultationController::class, 'updateStatus']);
});

// IoT 午睡センサー（サービス間 Bearer）
Route::middleware(['iot.token', 'throttle:120,1'])->group(function () {
    Route::post('/nap-sensors', [NapSensorController::class, 'store']);
});
