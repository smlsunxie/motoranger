<?php

use App\Http\Controllers\FrontController;
use App\Http\Controllers\RepairOrderQuoteController;
use Illuminate\Support\Facades\Route;

// 前台首頁
Route::get('/', [FrontController::class, 'home'])->name('home');

// 本地開發專用:供文件截圖工具快速登入(僅 local 環境存在)
if (app()->environment('local')) {
    Route::get('/dev-login', function () {
        auth()->login(\App\Models\User::where('email', 'admin@motoranger.test')->firstOrFail());
        session()->regenerate();

        return redirect('/admin');
    });
}

// 估價單列印(後台人員)
Route::get('/repair-orders/{repairOrder}/quote', RepairOrderQuoteController::class)
    ->middleware('auth')
    ->name('repair-orders.quote');

// 客人掃 QR Code 的公開單據頁(簽名網址)
Route::get('/q/{repairOrder}', [RepairOrderQuoteController::class, 'publicShow'])
    ->name('quote.public');

// 客人於公開頁留下備注(沿用同一組簽名)
Route::post('/q/{repairOrder}/notes', [RepairOrderQuoteController::class, 'addNote'])
    ->name('quote.public.note');
