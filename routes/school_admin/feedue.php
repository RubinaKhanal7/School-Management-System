<?php

use App\Http\Controllers\SchoolAdmin\FeeDueController;


Route::resource('fee-dues', FeeDueController::class);
Route::post('fee-dues/index', [FeeDueController::class, 'index'])->name('fee-dues');
Route::post('fee-dues/get', [FeeDueController::class, 'getAllFeeDues'])->name('fee-dues.get');
Route::get('/search-dueData', [FeeDueController::class, 'getAllSearchData'])->name('search-feedues.get');
