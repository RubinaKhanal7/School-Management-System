<?php
use App\Http\Controllers\SchoolAdmin\VoucherTypeController;

Route::get('voucher_types', [VoucherTypeController::class, 'index'])->name('voucher_types.index');
Route::get('voucher_types/create', [VoucherTypeController::class, 'create'])->name('voucher_types.create');
Route::post('voucher_types', [VoucherTypeController::class, 'store'])->name('voucher_types.store');
Route::get('voucher_types/{voucherType}/edit', [VoucherTypeController::class, 'edit'])->name('voucher_types.edit');
Route::put('voucher_types/{voucherType}', [VoucherTypeController::class, 'update'])->name('voucher_types.update');
Route::delete('voucher_types/{voucherType}', [VoucherTypeController::class, 'destroy'])->name('voucher_types.destroy');
