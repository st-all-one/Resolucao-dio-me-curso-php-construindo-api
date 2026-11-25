<?php

declare(strict_types=1);

namespace App\Domain\Band;

use App\Domain\Shared\DomainEvent;

/**
 * Entidade imutável do domínio. Identidade é opcional até persistência:
 * `withIdentity()` devolve uma nova instância com o id atribuído pelo repositório.
 */
final readonly class Band
{
    /**
     * @param  list<DomainEvent>  $events
     */
    public function __construct(
        public BandName $name,
        public Genre $genre,
        public FormedIn $formedIn,
        public ?BandId $id = null,
        public array $events = [],
    ) {}

    public function withIdentity(BandId $id): self
    {
        return new self($this->name, $this->genre, $this->formedIn, $id, $this->events);
    }

    public function withEvents(DomainEvent ...$events): self
    {
        return new self($this->name, $this->genre, $this->formedIn, $this->id, array_values([...$this->events, ...$events]));
    }

    public function isNew(): bool
    {
        return $this->id === null;
    }
}
