<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Band\BandId;
use App\Domain\Band\BandName;
use App\Domain\Band\FormedIn;
use App\Domain\Band\InvalidBandData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(BandName::class)]
#[CoversClass(BandId::class)]
#[CoversClass(FormedIn::class)]
#[CoversClass(InvalidBandData::class)]
final class ValueObjectTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function invalidNames(): iterable
    {
        yield 'empty' => [''];
        yield 'blank' => ['   '];
        yield 'too long' => [str_repeat('a', 121)];
    }

    #[DataProvider('invalidNames')]
    public function test_name_rejects_invalid_input(string $name): void
    {
        $this->expectException(InvalidBandData::class);

        new BandName($name);
    }

    public function test_name_keeps_original_value(): void
    {
        self::assertSame('  Pink Floyd  ', (new BandName('  Pink Floyd  '))->value);
    }

    public function test_id_must_be_positive(): void
    {
        $this->expectException(InvalidBandData::class);

        new BandId(0);
    }

    public function test_year_bounds(): void
    {
        self::assertSame(1965, (new FormedIn(1965))->value);

        $this->expectException(InvalidBandData::class);
        new FormedIn(1899);
    }
}
