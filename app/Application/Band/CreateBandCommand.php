<?php

declare(strict_types=1);

namespace App\Application\Band;

final readonly class CreateBandCommand
{
    public function __construct(
        public string $name,
        public string $genre,
        public int $formedIn,
    ) {}
}
