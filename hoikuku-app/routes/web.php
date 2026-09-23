<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ShiftMatrixController;
use App\Http\Controllers\Admin\ShiftOptimizationController as AdminShiftOptimizationController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\TodoTaskController;
use App\Http\Controllers\Admin\DocumentationReviewController;
use App\Http\Controllers\Admin\ChildFaceProfileController;
use App\Http\Controllers\AllergyController;
use App\Http\Controllers\CareLogController;
use App\Http\Controllers\ContactNoteController;
use App\Http\Controllers\DailyAttendanceController;
use App\Http\Controllers\GrowthAlbumController;
use App\Http\Controllers\NapCheckController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\ShiftPatternController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\StaffShiftSubmissionController;
use App\Http\Controllers\ShiftPeriodController;
use App\Http\Controllers\ChildController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // 認証済みユーザー向けのルートグループ内などに追記
    Route::get('/shifts/periods/create', [ShiftPeriodController::class, 'create'])->name('shifts.periods.create');
    Route::post('/shifts/periods', [ShiftPeriodController::class, 'store'])->name('shifts.periods.store');
   
    // 認証済みのユーザー（スタッフ・管理者）がアクセスできる児童管理ルート
    Route::resource('children', ChildController::class);

});

// 管理者・スタッフ向けシフト管理
Route::middleware(['auth', 'verified', 'role:staff,admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/shifts/matrix', [ShiftMatrixController::class, 'index']);

    // 2. 画面（View）を表示するルート ← ここに 'shifts.matrix' を綺麗に割り当てます
    Route::get('/shifts/matrix-view', function () {
        return view('admin.shifts.index');
    })->name('shifts.matrix');

    Route::get('/shifts/optimize', [AdminShiftOptimizationController::class, 'create'])->name('shifts.optimize');
    Route::post('/shifts/optimize', [AdminShiftOptimizationController::class, 'store'])->name('shifts.optimize.store');

    // ▼ 【追加】スタッフごとの個別シフト登録ルート
    Route::get('/shifts/create', [ShiftController::class, 'create'])->name('shifts.create');
    Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');

    // ▼ 【追加】園児（チャイルドID）の登録・管理用ルート
    Route::get('/children/create', [ChildController::class, 'create'])->name('children.create');
    Route::post('/children', [ChildController::class, 'store'])->name('children.store');

    // ▼ 登録用のルート
    Route::get('/staff/register', [StaffController::class, 'create'])->name('staff.register');
    Route::post('/staff/register', [StaffController::class, 'store'])->name('staff.store');

    Route::resource('shift_patterns', ShiftPatternController::class);



    // 顔認証レビュー・参照顔登録
    Route::get('/documentations/review', [DocumentationReviewController::class, 'index'])->name('documentations.review');
    Route::patch('/documentations/{documentation}/assign', [DocumentationReviewController::class, 'update'])->name('documentations.assign');
    Route::get('/children/faces', [ChildFaceProfileController::class, 'index'])->name('children.faces');
    Route::post('/children/{child}/faces', [ChildFaceProfileController::class, 'store'])->name('children.faces.store');
    Route::delete('/children/faces/{faceProfile}', [ChildFaceProfileController::class, 'destroy'])->name('children.faces.destroy');
});

// 支援ダッシュボード（スタッフは全員、保護者は自分の児童のみ）
Route::middleware(['auth', 'role:staff,admin,parent'])->group(function () {
    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
});

// 支援ログの記録はスタッフのみ
Route::middleware(['auth', 'role:staff,admin'])->group(function () {
    Route::post('/support/logs/{child}', [SupportController::class, 'storeLog'])->name('support.logs.store');
});

// 保護者向け相談画面（表示）
Route::middleware(['auth', 'role:parent'])->group(function () {
    Route::get('/consultations/create', [ConsultationController::class, 'create'])->name('consultations.create');
});

// 保護者向け AI 問い合わせ・相談送信（レート制限）
Route::middleware(['auth', 'role:parent', 'throttle:10,1'])->group(function () {
    Route::post('/consultations', [ConsultationController::class, 'store'])->name('consultations.store');
    Route::post('/inquiries', [InquiryController::class, 'store'])->name('inquiries.store');
});

// 保育士向け相談受信ボックス
Route::middleware(['auth', 'role:staff,admin,counselor'])->group(function () {
    Route::get('/admin/consultations', [ConsultationController::class, 'indexForStaff'])->name('admin.consultations.index');
});

// ドキュメンテーション一覧・写真配信
Route::middleware(['auth', 'role:staff,admin,parent,counselor'])->group(function () {
    Route::get('/documentations', [DocumentationController::class, 'index'])->name('documentations.index');
    Route::get('/documentations/{documentation}/photo', [DocumentationController::class, 'photo'])
        ->name('documentations.photo');
});

// スタッフ向け AI ドキュメンテーション生成（レート制限）
Route::middleware(['auth', 'role:staff,admin', 'throttle:20,1'])->group(function () {
    Route::post('/documentations', [DocumentationController::class, 'store'])->name('documentations.store');
});

// todo-app / action-list 連携（タスク一覧・作成・音声）
Route::middleware(['auth', 'verified', 'role:staff,admin'])->group(function () {
    Route::get('/todos', [TodoTaskController::class, 'index'])->name('todos.index');
    Route::post('/todos', [TodoTaskController::class, 'store'])->name('todos.store');
    Route::patch('/todos/{taskId}/toggle', [TodoTaskController::class, 'toggle'])->name('todos.toggle');
    Route::post('/todos/voice', [TodoTaskController::class, 'voice'])->name('todos.voice');
});

// ===== 保育オペレーション（連絡帳 / 午睡 / 登園 / アルバム / ケア / アレルギー） =====
Route::middleware(['auth', 'verified', 'role:staff,admin,parent'])->group(function () {
    Route::get('/contact-notes', [ContactNoteController::class, 'index'])->name('contact-notes.index');
    Route::get('/naps', [NapCheckController::class, 'index'])->name('naps.index');
    Route::get('/attendance', [DailyAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [DailyAttendanceController::class, 'upsert'])->name('attendance.upsert');
    Route::get('/albums', [GrowthAlbumController::class, 'index'])->name('albums.index');
    Route::get('/care-logs', [CareLogController::class, 'index'])->name('care-logs.index');

    Route::delete('/documentations/{id}', [DocumentationReviewController::class, 'destroy'])->name('documentations.destroy');
});

Route::middleware(['auth', 'verified', 'role:staff,admin', 'throttle:60,1'])->group(function () {
    Route::post('/contact-notes', [ContactNoteController::class, 'store'])->name('contact-notes.store');
    Route::post('/naps', [NapCheckController::class, 'store'])->name('naps.store');
    Route::get('/naps/alerts/feed', [NapCheckController::class, 'feed'])->name('naps.alerts.feed');
    Route::patch('/naps/alerts/{alert}', [NapCheckController::class, 'acknowledge'])->name('naps.alerts.ack');
    Route::post('/albums', [GrowthAlbumController::class, 'store'])->name('albums.store');
    Route::post('/care-logs', [CareLogController::class, 'store'])->name('care-logs.store');
    Route::get('/allergies', [AllergyController::class, 'index'])->name('allergies.index');
    Route::post('/allergies', [AllergyController::class, 'store'])->name('allergies.store');
    Route::post('/allergies/check', [AllergyController::class, 'checkServing'])->name('allergies.check');
});

//認証済みスタッフのみがアクセスできるように、ルートを設定
Route::middleware(['auth'])->prefix('staff')->name('staff.')->group(function () {
    // シフト希望入力画面
    Route::get('/shifts/{shiftPeriod}/create', [StaffShiftSubmissionController::class, 'create'])->name('shifts.create');
    // シフト希望保存処理
    Route::post('/shifts/{shiftPeriod}', [StaffShiftSubmissionController::class, 'store'])->name('shifts.store');
});

Route::get('/admin/shifts/periods', [App\Http\Controllers\ShiftPeriodController::class, 'index'])
    ->name('admin.shifts.periods.index');

Route::get('/admin/shifts/periods/json', [App\Http\Controllers\ShiftPeriodController::class, 'getPeriodsJson'])
    ->name('admin.shifts.periods.json');
    


// ❌ この部分は削除またはコメントアウトしてください
/*Route::middleware(['auth', 'verified'])->group(function () {
    // 管理者のみがアクセスできるスタッフ管理ルート
    Route::get('admin/staff/register', [StaffController::class, 'create'])
        ->name('staff.register');

    Route::post('admin/staff/register', [StaffController::class, 'store'])
        ->name('staff.store');
    
        // 管理者によるスタッフ追加機能
    Route::get('admin/staff/register', [StaffController::class, 'create'])->name('staff.register');
    Route::post('admin/staff/register', [StaffController::class, 'store']);
});
*/
require __DIR__ . '/auth.php';
