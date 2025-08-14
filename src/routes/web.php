<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\RequestController as AdminRequestController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    return redirect('/login');
});

//会員登録画面のルート
Route::get('/register', [RegisterController::class, 'create'])->name('register.form');
Route::post('/register', [RegisterController::class, 'store'])->name('register');

// ログインページのルート
Route::get('/login', [AuthController::class, 'create'])->name('login');
Route::post('/login', [AuthController::class, 'store']);

//メール認証機能
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request){
    $request->fulfill();
    return redirect('/attendance');
})->middleware(['auth', 'signed'])->name('verification.verify');


Route::post('/email/verify/resend', function (Request $request){
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送信しました');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

//勤怠登録画面へのルート
Route::middleware('auth', 'verified')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('dashboard');
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');
    Route::post('/break/start', [AttendanceController::class, 'restStart'])->name('rest.start');
    Route::post('/break/end', [AttendanceController::class, 'restEnd'])->name('rest.end');
});

//勤怠一覧画面へのルート
Route::middleware('auth', 'verified')->group(function () {
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    //Route::get('/attendance/{attendance}', [AttendanceController::class, 'show'])->name('attendance.show');
});

//勤怠詳細画面へのルート
Route::middleware('auth', 'verified')->group(function () {
    Route::get('/attendance/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::put('/attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
});

//申請内容を保存するためのルート
Route::post('/stamp_correction_request/store', [RequestController::class, 'store'])
    ->middleware('auth')
    ->name('request.store');

//申請一覧ページのルート
Route::get('/stamp_correction_request/list', [RequestController::class, 'index'])
    ->middleware('auth')
    ->name('requests.list');
Route::get('/stamp_correction_request/{request_id}', [RequestController::class, 'show'])
->middleware('auth')
->name('request.show');

//勤怠修正申請の承認ルート
Route::post('/requests/{request}/approve', [RequestController::class, 'approve'])->name('request.approve');
Route::post('/requests/{request}/reject', [RequestController::class, 'reject'])->name('request.reject');

//【グループ1】管理者認証ルート
Route::prefix('admin')->name('admin.')->group(function () {
    // ログイン画面表示（GET /admin/login）-> admin.login
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    // ログイン処理（POST /admin/login）
    Route::post('/login', [AdminLoginController::class, 'login']);
    // ログアウト処理（POST /admin/logout）-> admin.logout
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
    //スタッフ一覧表示
    Route::get('/staff', [App\Http\Controllers\Admin\StaffController::class, 'index'])->name('staff.index');
    //選択スタッフの月別勤怠詳細表示
    Route::get('/staff/{staff}', [App\Http\Controllers\Admin\StaffController::class, 'show'])->name('staff.show');
    //勤怠CSV出力
    Route::get('/staff/{staff}/export', [App\Http\Controllers\Admin\StaffController::class, 'exportCsv'])->name('staff.exportCsv');
});


//【グループ2】管理者向け勤怠管理ルート（要ログイン）
Route::middleware(['auth', 'can:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/attendance', [AdminAttendanceController::class, 'index'])->name('attendance.index');
    // 勤怠詳細ページ
    Route::get('/attendance/{attendance}', [AdminAttendanceController::class, 'show'])->name('attendance.show');
    Route::get('/staff/detail/{attendance}', [App\Http\Controllers\Admin\StaffController::class, 'showDetail'])->name('staff.detail');
    // 勤怠情報の上書き更新
    Route::put('/attendances/{attendance}', [App\Http\Controllers\Admin\AttendanceController::class, 'update'])->name('attendance.update');
    // 申請一覧ページのルート
    Route::get('/requests', [AdminRequestController::class, 'index'])->name('requests.index');
    // 詳細ページへのルート
    Route::get('/admin/requests/{request}', [AdminRequestController::class, 'show'])->name('requests.show');
    // 申請を承認するためのルート
    Route::patch('/admin/requests/{request}', [AdminRequestController::class, 'update'])->name('requests.update');
});