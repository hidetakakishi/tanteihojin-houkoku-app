<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Fortify;

use App\Http\Controllers\HearingSheetController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Admin\UserAdminController;

/*
|--------------------------------------------------------------------------
| Fortify view overrides
|--------------------------------------------------------------------------
*/
Fortify::loginView(fn () => view('auth.login'));
// 必要なら残す（画面からはリンクを出していないだけ）
Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));

/*
|--------------------------------------------------------------------------
| Root
| - ログイン前: / へ来ても login へ
| - ログイン後: / は home へ
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('home')
        : redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| 認証必須ルート
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Home
    Route::get('/home', fn () => view('main.home'))->name('home');

    // HearingSheet
    Route::get('/hearingsheet', fn () => view('hearingsheet.index'))->name('hearingsheet.index');
    Route::post('/hearingsheet/store', [HearingSheetController::class, 'store'])->name('hearingsheet.submit');

    Route::get('/hearingsheets/list', [HearingSheetController::class, 'list'])->name('hearingsheet.list');
    Route::get('/hearingsheets/edit/{id}', [HearingSheetController::class, 'edit'])->name('hearingsheet.edit');
    Route::put('/hearingsheets/{id}', [HearingSheetController::class, 'update'])->name('hearingsheet.update');
    Route::delete('/hearingsheets/{id}', [HearingSheetController::class, 'destroy'])->name('hearingsheet.destroy');
    Route::get('/hearingsheets/show/{id}', [HearingSheetController::class, 'show'])->name('hearingsheet.show');

    // Report
    Route::get('/hearingsheet/{id}/report/create', [ReportController::class, 'create'])->name('report.create');
    Route::post('/report/store', [ReportController::class, 'store'])->name('report.store');
    Route::get('/report/{id}/preview', [ReportController::class, 'preview'])->name('report.preview');
    Route::get('/report/preview/{id}/pdf', [ReportController::class, 'downloadPdf'])->name('report.download_pdf');

    // ※ 以前「お客様専用表示」だったものも、要件に合わせて認証下に移動
    Route::get('/report/view/{report_key}', [ReportController::class, 'publicForm'])->name('report.public.form');
    Route::post('/report/view/{report_key}', [ReportController::class, 'publicView'])->name('report.public.view');
});

/*
|--------------------------------------------------------------------------
| 管理者ルート
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::redirect('/', '/admin/users')->name('home');
        Route::resource('users', UserAdminController::class)->except(['show']);
    });

/*
|--------------------------------------------------------------------------
| Fallback
| - 未ログイン: どこへ行っても login に飛ばす
| - ログイン済み: 未定義URLは home に戻す
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return Auth::check()
        ? redirect()->route('home')
        : redirect()->route('login');
});