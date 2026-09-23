<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\MachineController;
use App\Http\Controllers\Web\MemoController;
use App\Http\Controllers\Web\ToolController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (Laravel Breeze Blade + 現場ドメイン)
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('machines', MachineController::class);
    Route::resource('memos', MemoController::class);
    Route::resource('tools', ToolController::class);
    Route::post('/tool-logs', [ToolController::class, 'storeLog'])->name('tool-logs.store');
});

require __DIR__.'/auth.php';
