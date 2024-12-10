<?php

use App\Http\Controllers\SchoolAdmin\FeeCollectionController;


Route::resource('fee-collections', FeeCollectionController::class);
Route::post('fee-collections/get', [FeeCollectionController::class, 'getAllFeeCollection'])->name('fee-collections.get');


Route::get('get-sections/{classId}', [FeeCollectionController::class, 'getSections'])->name('get-sections');

Route::post('get-studentscollection', [FeeCollectionController::class, 'getStudentsCollection'])->name('get-studentscollection');

Route::get('get-studentfees/{userId}', [FeeCollectionController::class, 'studentFee'])->name('get-studentfees');

Route::get('/student-fee-details/{studentSessionId}', [FeeCollectionController::class, 'getStudentFeeDetails']);
Route::post('fee-collections', [FeeCollectionController::class, 'store'])->name('fee-collections.store');



