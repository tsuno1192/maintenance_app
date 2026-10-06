<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RepairReportController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\TroubleController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});


Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/troubles', [TroubleController::class, 'index'])->name('troubles.index');
    Route::get('/troubles/create', [TroubleController::class, 'create'])->name('troubles.create');
    Route::post('/troubles', [TroubleController::class, 'store'])->name('troubles.store');
    Route::get('/troubles/{trouble}', [TroubleController::class, 'show'])->name('troubles.show');
    Route::get('/troubles/{trouble}/pdf', [TroubleController::class, 'pdf'])->name('troubles.pdf');
    Route::post('/troubles/{trouble}/approve', [TroubleController::class, 'approve'])->name('troubles.approve');

    Route::get('/troubles/{trouble}/repair-reports/create', [RepairReportController::class, 'create'])
        ->name('repair-reports.create');
    Route::post('/troubles/{trouble}/repair-reports', [RepairReportController::class, 'store'])
        ->name('repair-reports.store');
    Route::get('/repair-reports/{repairReport}', [RepairReportController::class, 'show'])
        ->name('repair-reports.show');
    Route::post('/repair-reports/{repairReport}/approve', [RepairReportController::class, 'approve'])
        ->name('repair-reports.approve');

    Route::get('/todos', [TodoController::class, 'index'])->name('todos.index');
    Route::post('/todos/{todo}/toggle', [TodoController::class, 'toggle'])->name('todos.toggle');

    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::post('/analytics/plan', [AnalyticsController::class, 'planInspections'])->name('analytics.plan');

    Route::resource('machines', MachineController::class);
    Route::resource('memos', MemoController::class);
    Route::post('/memos/{memo}/acknowledge', [MemoController::class, 'acknowledge'])->name('memos.acknowledge');
    Route::get('/memos/{memo}/images/{memoImage}', [MemoController::class, 'image'])->name('memos.images.show');
    Route::delete('/memos/{memo}/images/{memoImage}', [MemoController::class, 'destroyImage'])->name('memos.images.destroy');

    Route::resource('tools', ToolController::class);
    Route::post('/tools/{tool}/logs', [ToolController::class, 'storeLog'])->name('tools.logs.store');
});

require __DIR__.'/auth.php';