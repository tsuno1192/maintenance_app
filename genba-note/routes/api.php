<?php

use App\Http\Controllers\Api\MachineController;
use App\Http\Controllers\Api\MemoController;
use App\Http\Controllers\Api\ToolController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| 将来の SPA / モバイル連携向け REST API。
| 認証は web セッション共有（Sanctum 導入時は auth:sanctum へ置換予定）。
|
*/

/*
| 将来の SPA / モバイル連携向け REST API。
| 認証に Laravel Sanctum を使用。
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('machines', MachineController::class);
    Route::apiResource('memos', MemoController::class);
    Route::apiResource('tools', ToolController::class);
    Route::post('tool-logs', [ToolController::class, 'storeLog'])->name('api.tool-logs.store');
});

// 必要に応じて Sanctum などの認証ミドルウェアを通す場合は `auth:sanctum` を指定します
// Route::middleware('auth:sanctum')->group(function () {
    
    // 設備 API
    Route::apiResource('machines', MachineController::class);

    // メモ（申し送り） API
    Route::apiResource('memos', MemoController::class);
    Route::post('memos/{memo}/acknowledge', [MemoController::class, 'acknowledge']);

    // 工具 API
    Route::apiResource('tools', ToolController::class);
    Route::post('tools/{tool}/logs', [ToolController::class, 'storeLog']);

// });