<?php

use App\Http\Controllers\Api\InternalNoteController;
use App\Http\Controllers\Api\MacroController;
use App\Http\Controllers\InternalNoteController as InternalNoteWebController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'ext.auth'])->group(function () {
    Route::get('internal-notes/{ticket}', [InternalNoteController::class, 'index'])
        ->name('api.internal-notes.index');
    Route::post('internal-notes', [InternalNoteWebController::class, 'store'])
        ->name('api.internal-notes.store');
    Route::get('macros', [MacroController::class, 'index'])
        ->name('api.macros.index');
});
