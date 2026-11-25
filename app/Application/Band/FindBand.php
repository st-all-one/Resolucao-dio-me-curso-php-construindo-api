<?php

declare(strict_types=1);

namespace App\Application\Band;

use App\Domain\Band\Band;
use App\Domain\Band\BandId;
use App\Domain\Band\BandNotFound;
use App\Domain\Band\BandRepository;

final readonly class FindBand
{
    public function __construct(private BandRepository $bands) {}

    public function __invoke(int $identifier): Band
    {
        return $this->bands->findById(new BandId($identifier))
            ?? throw BandNotFound::withId(new BandId($identifier));
    }
}
