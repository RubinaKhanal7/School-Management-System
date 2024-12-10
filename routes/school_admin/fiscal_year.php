<?php
use App\Http\Controllers\SchoolAdmin\FiscalYearController;

Route::get('fiscal-years', [FiscalYearController::class, 'index'])->name('fiscal-years.index');
Route::get('fiscal-years/create', [FiscalYearController::class, 'create'])->name('fiscal-years.create');
Route::post('fiscal-years', [FiscalYearController::class, 'store'])->name('fiscal-years.store');
Route::get('fiscal-years/{id}/edit', [FiscalYearController::class, 'edit'])->name('fiscal-years.edit');
Route::put('fiscal-years/{id}', [FiscalYearController::class, 'update'])->name('fiscal-years.update');
Route::delete('fiscal-years/{id}', [FiscalYearController::class, 'destroy'])->name('fiscal-years.destroy');
