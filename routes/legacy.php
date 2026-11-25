<?php

declare(strict_types=1);

use App\Infrastructure\Http\Controllers\LegacyBandController;
use Illuminate\Support\Facades\Route;

// Contrato da live DIO (5.7): redirecionamento permanente, sem middleware web.
// Actions de classe são obrigatórias para `php artisan optimize` (route:cache).
Route::middleware('api')->group(static function (): void {
    Route::get('/bands', [LegacyBandController::class, 'index'])->name('legacy.bands.index');
    Route::post('/bands/store', [LegacyBandController::class, 'store'])->name('legacy.bands.store');
    Route::get('/bands/gender/{gender}', [LegacyBandController::class, 'byGenre'])->name('legacy.bands.by-genre');
    Route::get('/bands/{band}', [LegacyBandController::class, 'show'])
        ->whereNumber('band')
        ->name('legacy.bands.show');
});
