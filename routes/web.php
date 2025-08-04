<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Fortify;
use App\Http\Controllers\HearingSheetController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return view('welcome');
});

Fortify::loginView(fn () => view('auth.login'));
// Fortify::registerView(fn () => view('auth.register')); // アカウント登録画面
Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));

Route::middleware(['auth'])->group(function () {
    Route::get('/home', function () {
        return view('main.home');
    })->name('home');

    Route::get('/hearingsheet', fn() => view('hearingsheet.index'))->name('hearingsheet.index');
    Route::get('/hearingsheets/list', [HearingSheetController::class, 'list'])->name('hearingsheet.list');
    Route::get('/hearingsheets/show/{id}', [HearingSheetController::class, 'show'])->name('hearingsheet.show');
    Route::post('/hearingsheet/store', [HearingSheetController::class, 'store'])->name('hearingsheet.submit');
    Route::get('/hearingsheet/{id}/report/create', [ReportController::class, 'create'])->name('report.create');
    Route::get('/report/{id}/preview', [ReportController::class, 'preview'])->name('report.preview');
    Route::post('/report/store', [ReportController::class, 'store'])->name('report.store');
    Route::get('/report/preview/{id}/pdf', [App\Http\Controllers\ReportController::class, 'downloadPdf'])->name('report.download_pdf');
});