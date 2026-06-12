<?php

use App\Http\Controllers\RepairOrderQuoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

// 估價單列印(後台人員)
Route::get('/repair-orders/{repairOrder}/quote', RepairOrderQuoteController::class)
    ->middleware('auth')
    ->name('repair-orders.quote');

// 客人掃 QR Code 的公開單據頁(簽名網址)
Route::get('/q/{repairOrder}', [RepairOrderQuoteController::class, 'publicShow'])
    ->name('quote.public');
