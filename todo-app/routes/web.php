<?php

use App\Http\Controllers\AccessController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AccessController::class, 'create'])->name('login');
Route::post('/login', [AccessController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('login.store');

Route::post('/logout', [AccessController::class, 'destroy'])->name('logout');

Route::redirect('/', '/tasks');

Route::middleware(['action.list', 'throttle:60,1'])->group(function () {
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
    Route::post('/tasks/voice', [TaskController::class, 'voice'])
        ->middleware('throttle:20,1')
        ->name('tasks.voice');
});
