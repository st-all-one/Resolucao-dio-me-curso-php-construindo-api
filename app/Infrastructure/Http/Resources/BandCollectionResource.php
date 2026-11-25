<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Resources;

use App\Application\Band\BandCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BandCollection
 */
final class BandCollectionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var BandCollection $collection */
        $collection = $this->resource;

        return [
            'data' => BandResource::collection($collection->items),
            'meta' => [
                'total' => $collection->total,
                'page' => $collection->page,
                'per_page' => $collection->perPage,
                'total_pages' => $collection->totalPages(),
            ],
        ];
    }
}
