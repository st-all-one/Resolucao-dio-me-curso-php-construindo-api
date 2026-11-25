<?php

declare(strict_types=1);

use App\Infrastructure\Http\Controllers\BandController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(static function (): void {
    Route::get('/bands', [BandController::class, 'index'])->name('bands.index');
    Route::get('/bands/gender/{gender}', [BandController::class, 'byGenre'])->name('bands.by-genre');
    Route::get('/bands/{band}', [BandController::class, 'show'])
        ->whereNumber('band')
        ->name('bands.show');
    Route::post('/bands', [BandController::class, 'store'])->name('bands.store');
});
