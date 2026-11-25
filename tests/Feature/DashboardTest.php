<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Infrastructure\Http\Controllers\DashboardController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(DashboardController::class)]
final class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_dashboard_renders_bands_from_domain(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Bandas', false)
            ->assertSee('Pink Floyd', false)
            ->assertSee('Miles Davis Quintet', false)
            ->assertSee('Filtrar por gênero', false)
            ->assertSee('css/dashboard.css', false);
    }

    public function test_dashboard_filters_by_genre_with_get(): void
    {
        $this->get('/?genre=rock')
            ->assertOk()
            ->assertSee('Pink Floyd', false)
            ->assertSee('Nirvana', false)
            ->assertDontSee('Daft Punk', false);
    }

    public function test_dashboard_shows_empty_state_for_genre_without_bands(): void
    {
        $this->get('/?genre=classical')
            ->assertOk()
            ->assertSee('Nenhuma banda encontrada', false)
            ->assertSee('Ver todas as bandas', false);
    }

    public function test_dashboard_ignores_unknown_genre_without_error(): void
    {
        $this->get('/?genre=invalido')
            ->assertOk()
            ->assertSee('Pink Floyd', false);
    }
}
