<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use Illuminate\Http\RedirectResponse;

/**
 * Aliases da live DIO. Action de classe (não closure) para sobreviver a
 * `php artisan optimize` / `route:cache`.
 */
final readonly class LegacyBandController
{
    public function index(): RedirectResponse
    {
        return redirect()->route('bands.index', [], 308);
    }

    public function store(): RedirectResponse
    {
        return redirect()->route('bands.store', [], 308);
    }

    public function byGenre(string $gender): RedirectResponse
    {
        return redirect()->route('bands.by-genre', ['gender' => $gender], 308);
    }

    public function show(int $band): RedirectResponse
    {
        return redirect()->route('bands.show', ['band' => $band], 308);
    }
}
