<?php


use App\Http\Controllers\SchoolAdmin\InventoriesController;


Route::resource('inventories', InventoriesController::class);
Route::post('inventories/get', [InventoriesController::class, 'getAllInventories'])->name('inventories.get');
Route::get('/admin/sources/get-all', [InventoriesController::class, 'getAllSources'])->name('sources.get-all');





