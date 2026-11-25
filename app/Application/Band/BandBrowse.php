<?php

declare(strict_types=1);

namespace App\Application\Band;

use App\Domain\Band\Genre;

final readonly class BandBrowse
{
    /**
     * @param  list<Genre>  $genres
     */
    public function __construct(
        public BandCollection $bands,
        public ?Genre $genre,
        public array $genres,
    ) {}
}
