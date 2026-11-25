<?php

declare(strict_types=1);

namespace App\Infrastructure\Band;

use App\Domain\Band\Band;
use App\Domain\Band\BandId;
use App\Domain\Band\BandName;
use App\Domain\Band\FormedIn;
use App\Domain\Band\Genre;

final readonly class EloquentBandMapper
{
    public function toDomain(\stdClass $row): Band
    {
        if (! isset($row->id, $row->name, $row->genre, $row->formed_in)
            || ! is_scalar($row->id)
            || ! is_string($row->name)
            || ! is_string($row->genre)
            || ! is_scalar($row->formed_in)) {
            throw new \LogicException('Band row has an unexpected shape.');
        }

        return new Band(
            name: new BandName($row->name),
            genre: Genre::fromValue($row->genre),
            formedIn: new FormedIn((int) $row->formed_in),
            id: new BandId((int) $row->id),
        );
    }

    /**
     * @return array{name: string, genre: string, formed_in: int}
     */
    public function toRecord(Band $band): array
    {
        return [
            'name' => $band->name->value,
            'genre' => $band->genre->value,
            'formed_in' => $band->formedIn->value,
        ];
    }
}
