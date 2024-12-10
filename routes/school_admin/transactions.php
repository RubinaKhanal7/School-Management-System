<?php
use App\Http\Controllers\SchoolAdmin\TransactionController;
use App\Http\Controllers\SchoolAdmin\DateController;

Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
Route::get('transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
Route::post('transactions', [TransactionController::class, 'store'])->name('transactions.store');
Route::get('/transactions/{id}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
Route::put('transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
Route::get('/admin/transactions/data', [TransactionController::class, 'getData'])->name('transactions.data');
Route::get('transactions/{voucherNo}/print', [TransactionController::class, 'print'])->name('transactions.print');
Route::get('transactions/export/excel', [TransactionController::class, 'exportExcel'])
    ->name('transactions.export.excel');
Route::get('transactions/export/pdf', [TransactionController::class, 'exportPdf'])
    ->name('transactions.export.pdf');
