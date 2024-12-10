<?php
use App\Http\Controllers\SchoolAdmin\AccountController;

Route::get('/', [AccountController::class, 'index'])->name('accounts.index');
Route::get('/create', [AccountController::class, 'create'])->name('accounts.create');
Route::post('/', [AccountController::class, 'store'])->name('accounts.store');
Route::get('/{account}/edit', [AccountController::class, 'edit'])->name('accounts.edit');
Route::put('/{account}', [AccountController::class, 'update'])->name('accounts.update');
Route::delete('/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');
