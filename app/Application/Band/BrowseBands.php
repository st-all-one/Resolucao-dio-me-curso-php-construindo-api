<?php

declare(strict_types=1);

namespace App\Application\Band;

use App\Domain\Band\BandNotFound;
use App\Domain\Band\Genre;

/**
 * Caso de uso da tela: combina a listagem e o filtro sem expor detalhes HTTP.
 * Um gênero desconhecido é tratado como "sem filtro" — a tela não deve virar 422.
 */
final readonly class BrowseBands
{
    public function __construct(
        private ListBands $listBands,
        private ListBandsByGenre $listByGenre,
    ) {}

    public function __invoke(?string $genre): BandBrowse
    {
        $selected = $genre === null ? null : Genre::tryFrom($genre);

        if ($selected === null) {
            return new BandBrowse(
                bands: ($this->listBands)(page: 1, perPage: 50),
                genre: null,
                genres: Genre::cases(),
            );
        }

        try {
            $bands = ($this->listByGenre)($selected->value, page: 1, perPage: 50);
        } catch (BandNotFound) {
            $bands = new BandCollection(items: [], total: 0, page: 1, perPage: 50);
        }

        return new BandBrowse(
            bands: $bands,
            genre: $selected,
            genres: Genre::cases(),
        );
    }
}
