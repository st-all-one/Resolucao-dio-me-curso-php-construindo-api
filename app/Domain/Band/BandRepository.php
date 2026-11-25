<?php

declare(strict_types=1);

namespace App\Domain\Band;

interface BandRepository
{
    /**
     * @return list<Band>
     */
    public function all(int $limit, int $offset): array;

    public function count(): int;

    public function findById(BandId $id): ?Band;

    /**
     * @return list<Band>
     */
    public function findByGenre(Genre $genre, int $limit, int $offset): array;

    public function countByGenre(Genre $genre): int;

    public function save(Band $band): Band;
}
