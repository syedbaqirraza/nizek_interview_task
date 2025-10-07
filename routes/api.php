<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StockController;

Route::prefix('stocks')->group(function () {
    Route::get('/{company}/changes', [StockController::class, 'getPeriodChanges']);
    Route::get('/{company}/compare', [StockController::class, 'getCustomDateComparison']);
});