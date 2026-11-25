<?php

declare(strict_types=1);

namespace App\Application\Band;

use App\Domain\Band\Band;
use App\Domain\Band\BandCreated;
use App\Domain\Band\BandName;
use App\Domain\Band\BandRepository;
use App\Domain\Band\FormedIn;
use App\Domain\Band\Genre;
use App\Domain\Shared\DomainEventDispatcher;

final readonly class CreateBand
{
    public function __construct(
        private BandRepository $bands,
        private DomainEventDispatcher $events,
    ) {}

    public function __invoke(CreateBandCommand $command): Band
    {
        $band = new Band(
            name: new BandName($command->name),
            genre: Genre::fromValue($command->genre),
            formedIn: new FormedIn($command->formedIn),
        );

        $saved = $this->bands->save($band);
        $event = new BandCreated(
            id: $saved->id ?? throw new \LogicException('Repository must assign an id.'),
            name: $saved->name,
            genre: $saved->genre,
        );

        $this->events->dispatch($event);

        return $saved->withEvents($event);
    }
}
