<?php

declare(strict_types=1);

namespace App\Application\Band;

use App\Domain\Band\BandNotFound;
use App\Domain\Band\BandRepository;
use App\Domain\Band\Genre;

final readonly class ListBandsByGenre
{
    public function __construct(private BandRepository $bands) {}

    public function __invoke(string $genre, int $page, int $perPage): BandCollection
    {
        $value = Genre::fromValue($genre);
        $total = $this->bands->countByGenre($value);

        if ($total === 0) {
            throw BandNotFound::withGenre($value);
        }

        return new BandCollection(
            items: $this->bands->findByGenre($value, $perPage, ($page - 1) * $perPage),
            total: $total,
            page: $page,
            perPage: $perPage,
        );
    }
}
