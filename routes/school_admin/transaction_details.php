<?php
use App\Http\Controllers\SchoolAdmin\TransactionDetailController;

Route::get('/transaction-details', [TransactionDetailController::class, 'index'])->name('transaction_details.index');
Route::get('/transaction-details/create', [TransactionDetailController::class, 'create'])->name('transaction_details.create');
Route::post('/transaction-details', [TransactionDetailController::class, 'store'])->name('transaction_details.store');
Route::get('/transaction-details/{id}/edit', [TransactionDetailController::class, 'edit'])->name('transaction_details.edit');
Route::put('/transaction-details/{id}', [TransactionDetailController::class, 'update'])->name('transaction_details.update');
Route::delete('/transaction-details/{id}', [TransactionDetailController::class, 'destroy'])->name('transaction_details.destroy');
Route::get('/transaction-details/data', [TransactionDetailController::class, 'getTransactionDetails'])->name('transaction_details.getTransactionDetails');
