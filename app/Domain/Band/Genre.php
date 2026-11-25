<?php

declare(strict_types=1);

namespace App\Domain\Band;

/**
 * Gêneros musicais aceitos pelo domínio. O valor de serialização é o slug
 * usado na API e no banco de dados — nunca o nome da constante.
 */
enum Genre: string
{
    case Rock = 'rock';
    case Pop = 'pop';
    case Jazz = 'jazz';
    case Metal = 'metal';
    case Indie = 'indie';
    case Electronic = 'electronic';
    case HipHop = 'hip_hop';
    case Classical = 'classical';
    case Other = 'other';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromValue(string $value): self
    {
        return self::tryFrom($value) ?? throw InvalidBandData::invalidGenre($value);
    }

    public function label(): string
    {
        return match ($this) {
            self::HipHop => 'Hip Hop',
            self::Rock => 'Rock',
            self::Pop => 'Pop',
            self::Jazz => 'Jazz',
            self::Metal => 'Metal',
            self::Indie => 'Indie',
            self::Electronic => 'Electronic',
            self::Classical => 'Classical',
            self::Other => 'Other',
        };
    }
}
