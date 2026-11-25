<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Band\Genre;
use App\Domain\Band\InvalidBandData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Genre::class)]
final class GenreTest extends TestCase
{
    public function test_values_are_the_api_slugs(): void
    {
        self::assertContains('rock', Genre::values());
        self::assertContains('hip_hop', Genre::values());
        self::assertNotContains('HipHop', Genre::values());
    }

    public function test_from_value_rejects_unknown_genre(): void
    {
        $this->expectException(InvalidBandData::class);
        $this->expectExceptionMessage('Unknown genre "samba".');

        Genre::fromValue('samba');
    }

    public function test_human_label(): void
    {
        self::assertSame('Hip Hop', Genre::HipHop->label());
    }
}
