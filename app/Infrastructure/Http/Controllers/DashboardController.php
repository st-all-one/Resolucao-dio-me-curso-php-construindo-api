<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Application\Band\BrowseBands;
use App\Domain\Band\Genre;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final readonly class DashboardController
{
    public function __construct(private BrowseBands $browse) {}

    public function __invoke(Request $request): View
    {
        $genre = $request->query('genre');
        $value = is_string($genre) && Genre::tryFrom($genre) !== null ? $genre : null;

        return view('dashboard', [
            'browse' => ($this->browse)($value),
        ]);
    }
}
