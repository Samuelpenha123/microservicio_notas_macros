<?php

use App\Http\Controllers\Auth\ExternalAuthController;
use App\Http\Controllers\InternalNoteController;
use App\Http\Controllers\MacroController;
use App\Http\Controllers\MacroUsageController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::view('/docs', 'swagger')->name('docs');

Route::middleware('ext.guest')->group(function () {
    Route::get('/', fn () => redirect()->route('login'));
    Route::get('/login', [ExternalAuthController::class, 'create'])->name('login');
    Route::post('/login', [ExternalAuthController::class, 'store'])->name('login.store');
});

Route::middleware('ext.auth')->group(function () {
    Route::post('/logout', [ExternalAuthController::class, 'destroy'])->name('logout');

    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/responses', [TicketController::class, 'storeResponse'])
        ->name('tickets.responses.store');

    Route::post('/internal-notes', [InternalNoteController::class, 'store'])->name('internal-notes.store');
    Route::put('/internal-notes/{internalNote}', [InternalNoteController::class, 'update'])->name('internal-notes.update');
    Route::delete('/internal-notes/{internalNote}', [InternalNoteController::class, 'destroy'])
        ->name('internal-notes.destroy');

    Route::post('/macros', [MacroController::class, 'store'])->name('macros.store');
    Route::put('/macros/{macro}', [MacroController::class, 'update'])->name('macros.update');
    Route::delete('/macros/{macro}', [MacroController::class, 'destroy'])->name('macros.destroy');
    Route::post('/macros/{macro}/favorite', [MacroController::class, 'toggleFavorite'])->name('macros.favorite');

    Route::post('/macros/{macro}/preview', [MacroUsageController::class, 'preview'])->name('macros.preview');
    Route::post('/macros/{macro}/usages', [MacroUsageController::class, 'store'])->name('macros.usages.store');
    Route::post('/macro-usages/{macroUsage}/feedback', [MacroUsageController::class, 'feedback'])
        ->name('macros.usages.feedback');
});
