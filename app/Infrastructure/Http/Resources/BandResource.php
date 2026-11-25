<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Resources;

use App\Domain\Band\Band;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Band
 */
final class BandResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id?->value,
            'name' => $this->name->value,
            'genre' => $this->genre->value,
            'genre_label' => $this->genre->label(),
            'formed_in' => $this->formedIn->value,
        ];
    }
}
