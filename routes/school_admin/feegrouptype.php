<?php


use App\Http\Controllers\SchoolAdmin\FeeGroupTypesController;

Route::resource('fee-grouptypes', FeeGroupTypesController::class);
Route::post('fee-grouptypes/get', [FeeGroupTypesController::class, 'getAllFeeGroupsType'])->name('fee-grouptypes.get');
Route::get('fee-assignstudents', [FeeGroupTypesController::class, 'assignStudent'])->name('fee-assignstudents');

Route::get('fee-grouptypes/assign/{fee_group_id}', [FeeGroupTypesController::class, 'assignStudent'])->name('fee-assignstudents');
Route::post('admin/fee-grouptypes/store-assignment', [FeeGroupTypesController::class, 'storeAssignment'])
    ->name('fee-grouptypes.store-assignment');

