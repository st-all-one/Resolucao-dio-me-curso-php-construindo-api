<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Band\Band;
use App\Domain\Band\BandCreated;
use App\Domain\Band\BandId;
use App\Domain\Band\BandName;
use App\Domain\Band\FormedIn;
use App\Domain\Band\Genre;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Band::class)]
#[CoversClass(BandCreated::class)]
final class BandTest extends TestCase
{
    public function test_new_band_has_no_identity(): void
    {
        $band = new Band(new BandName('Nirvana'), Genre::Rock, new FormedIn(1987));

        self::assertTrue($band->isNew());
        self::assertNull($band->id);
    }

    public function test_with_identity_is_immutable(): void
    {
        $band = new Band(new BandName('Nirvana'), Genre::Rock, new FormedIn(1987));
        $persisted = $band->withIdentity(new BandId(7));

        self::assertTrue($band->isNew());
        self::assertSame(7, $persisted->id?->value);
        self::assertNotSame($band, $persisted);
    }

    public function test_events_are_accumulated(): void
    {
        $band = new Band(new BandName('Nirvana'), Genre::Rock, new FormedIn(1987));
        $event = new BandCreated(new BandId(1), $band->name, $band->genre);

        $withEvent = $band->withEvents($event);

        self::assertCount(0, $band->events);
        self::assertSame([$event], $withEvent->events);
        self::assertSame($event->id->value, $withEvent->events[0]->id->value);
    }
}
