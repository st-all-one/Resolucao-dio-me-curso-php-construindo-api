<?php

declare(strict_types=1);

namespace App\Application\Band;

use App\Domain\Band\Band;

final readonly class BandCollection
{
    /**
     * @param  list<Band>  $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $perPage,
    ) {}

    public function totalPages(): int
    {
        return (int) ceil($this->total / $this->perPage);
    }
}
