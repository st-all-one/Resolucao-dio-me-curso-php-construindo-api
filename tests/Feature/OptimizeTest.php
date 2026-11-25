<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Infrastructure\Http\Controllers\LegacyBandController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\PendingCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(LegacyBandController::class)]
final class OptimizeTest extends TestCase
{
    use RefreshDatabase;

    public function test_routes_are_cacheable_and_legacy_redirect_survives(): void
    {
        $this->seed();

        // O comando falha se qualquer action de rota for closure.
        $pending = $this->artisan('route:cache');
        self::assertInstanceOf(PendingCommand::class, $pending);
        $pending->assertExitCode(0);

        try {
            $this->get('/bands')->assertStatus(308)->assertRedirect('/api/v1/bands');
            $this->get('/')->assertOk()->assertSee('Pink Floyd', false);
        } finally {
            $clear = $this->artisan('route:clear');
            self::assertInstanceOf(PendingCommand::class, $clear);
            $clear->assertExitCode(0);
        }
    }
}
