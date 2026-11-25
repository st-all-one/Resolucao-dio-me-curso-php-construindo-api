<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Application\Band\CreateBand;
use App\Application\Band\FindBand;
use App\Application\Band\ListBands;
use App\Application\Band\ListBandsByGenre;
use App\Infrastructure\Http\Requests\StoreBandRequest;
use App\Infrastructure\Http\Resources\BandCollectionResource;
use App\Infrastructure\Http\Resources\BandResource;
use Illuminate\Http\JsonResponse;

final readonly class BandController
{
    public function __construct(
        private ListBands $listBands,
        private FindBand $findBand,
        private ListBandsByGenre $listByGenre,
        private CreateBand $createBand,
    ) {}

    public function index(): BandCollectionResource
    {
        BandResource::withoutWrapping();

        return new BandCollectionResource($this->listBands->__invoke(page: 1, perPage: 50));
    }

    public function show(int $band): BandResource
    {
        BandResource::withoutWrapping();

        return new BandResource($this->findBand->__invoke($band));
    }

    public function byGenre(string $gender): BandCollectionResource
    {
        BandResource::withoutWrapping();

        return new BandCollectionResource($this->listByGenre->__invoke($gender, page: 1, perPage: 50));
    }

    public function store(StoreBandRequest $request): JsonResponse
    {
        BandResource::withoutWrapping();

        $band = $this->createBand->__invoke($request->toCommand());

        return (new BandResource($band))
            ->response()
            ->setStatusCode(201)
            ->header('Location', '/api/v1/bands/' . $band->id?->value);
    }
}
