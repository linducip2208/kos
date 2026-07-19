<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])->prefix('api/v1/finance')->name('api.finance.')->group(function () {
    //
});
