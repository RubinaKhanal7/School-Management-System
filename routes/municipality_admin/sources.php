<?php
use App\Http\Controllers\MunicipalityAdmin\SourceController;

Route::get('municipality_admin/sources', [SourceController::class, 'index'])->name('sources.index');
Route::get('municipality_admin/sources/create', [SourceController::class, 'create'])->name('sources.create'); 
Route::post('municipality_admin/sources', [SourceController::class, 'store'])->name('sources.store');
Route::get('municipality_admin/sources/{source}/edit', [SourceController::class, 'edit'])->name('sources.edit');
Route::put('municipality_admin/sources/{source}', [SourceController::class, 'update'])->name('sources.update'); 
Route::delete('municipality_admin/sources/{source}', [SourceController::class, 'destroy'])->name('sources.destroy');
Route::post('admin/sources/get', [SourceController::class, 'get'])->name('sources.get');
