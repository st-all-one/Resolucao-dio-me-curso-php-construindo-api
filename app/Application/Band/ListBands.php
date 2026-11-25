<?php

declare(strict_types=1);

namespace App\Application\Band;

use App\Domain\Band\BandRepository;

final readonly class ListBands
{
    public function __construct(private BandRepository $bands) {}

    public function __invoke(int $page, int $perPage): BandCollection
    {
        $total = $this->bands->count();

        return new BandCollection(
            items: $this->bands->all(limit: $perPage, offset: ($page - 1) * $perPage),
            total: $total,
            page: $page,
            perPage: $perPage,
        );
    }
}
