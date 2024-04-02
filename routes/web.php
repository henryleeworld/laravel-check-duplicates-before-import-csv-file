<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');
Route::middleware('auth')->group(function () {
    Route::get('/', [ContactController::class, 'index'])->name('contacts.index');
    Route::post('/import_parse', [ImportController::class, 'parseImport'])->name('import_parse');
    Route::post('/import_process', [ImportController::class, 'processImport'])->name('import_process');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
