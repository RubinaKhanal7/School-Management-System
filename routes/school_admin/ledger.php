<?php
use App\Http\Controllers\SchoolAdmin\LedgerController;

Route::get('/ledgers', [LedgerController::class, 'index'])->name('ledgers.index');
Route::get('admin/ledgers/entries', [LedgerController::class, 'getLedgerEntries'])->name('admin.ledgers.entries');
