<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;

use App\Application\Band\CreateBandCommand;
use App\Domain\Band\FormedIn;
use App\Domain\Band\Genre;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreBandRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'genre' => ['required', 'string', Rule::in(Genre::values())],
            'formed_in' => [
                'required',
                'integer:strict',
                'min:' . FormedIn::MINIMUM_YEAR,
                'max:' . FormedIn::maximumYear(),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'in' => 'The selected :attribute is invalid.',
            'max' => 'The :attribute field must not exceed :max.',
            'min' => 'The :attribute field must be at least :min.',
        ];
    }

    public function toCommand(): CreateBandCommand
    {
        $validated = $this->validated();
        $name = $validated['name'] ?? null;
        $genre = $validated['genre'] ?? null;
        $formedIn = $validated['formed_in'] ?? null;

        if (! is_string($name) || ! is_string($genre) || ! is_int($formedIn)) {
            throw new \LogicException('Validated payload has an unexpected shape.');
        }

        return new CreateBandCommand(name: $name, genre: $genre, formedIn: $formedIn);
    }
}
