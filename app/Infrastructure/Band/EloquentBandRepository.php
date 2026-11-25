<?php

declare(strict_types=1);

namespace App\Infrastructure\Band;

use App\Domain\Band\Band;
use App\Domain\Band\BandId;
use App\Domain\Band\BandRepository;
use App\Domain\Band\Genre;
use App\Models\BandRecord;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final readonly class EloquentBandRepository implements BandRepository
{
    public function __construct(private EloquentBandMapper $mapper) {}

    public function all(int $limit, int $offset): array
    {
        /** @var list<Band> $bands */
        $bands = [];

        foreach ($this->query()->orderBy('id')->limit($limit)->offset($offset)->get() as $row) {
            $bands[] = $this->mapper->toDomain($row);
        }

        return $bands;
    }

    public function count(): int
    {
        return (int) $this->query()->count();
    }

    public function findById(BandId $id): ?Band
    {
        $row = $this->query()->where('id', $id->value)->first();

        return $row === null ? null : $this->mapper->toDomain($row);
    }

    public function findByGenre(Genre $genre, int $limit, int $offset): array
    {
        /** @var list<Band> $bands */
        $bands = [];

        foreach ($this->query()->where('genre', $genre->value)->orderBy('id')->limit($limit)->offset($offset)->get() as $row) {
            $bands[] = $this->mapper->toDomain($row);
        }

        return $bands;
    }

    public function countByGenre(Genre $genre): int
    {
        return (int) $this->query()->where('genre', $genre->value)->count();
    }

    public function save(Band $band): Band
    {
        $id = $this->query()->insertGetId($this->mapper->toRecord($band));
        $row = $this->query()->where('id', $id)->firstOrFail();

        return $this->mapper->toDomain($row);
    }

    private function query(): Builder
    {
        return DB::connection()->table((new BandRecord)->getTable());
    }
}
