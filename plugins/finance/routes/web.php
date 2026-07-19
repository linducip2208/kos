<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('finance')->name('finance.')->group(function () {
    Route::get('/reports', fn () => view('finance::reports.index'))->name('reports.index');
});
