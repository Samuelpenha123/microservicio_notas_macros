<?php

use App\Http\Controllers\Api\InternalNoteController;
use App\Http\Controllers\Api\MacroController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'ext.auth'])->group(function () {
    Route::get('internal-notes/{ticket}', [InternalNoteController::class, 'index'])
        ->name('api.internal-notes.index');
    Route::get('macros', [MacroController::class, 'index'])
        ->name('api.macros.index');
});
