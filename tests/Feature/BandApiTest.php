<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Infrastructure\Http\Controllers\BandController;
use App\Infrastructure\Queue\IndexBand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(BandController::class)]
final class BandApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Bus::fake([IndexBand::class]);
    }

    public function test_lists_bands(): void
    {
        $this->getJson('/api/v1/bands')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 5)
            ->assertJsonPath('data.0.name', 'Pink Floyd')
            ->assertHeader('X-Request-Id');
    }

    public function test_finds_band_by_id(): void
    {
        $this->getJson('/api/v1/bands/1')
            ->assertOk()
            ->assertJsonPath('name', 'Pink Floyd')
            ->assertJsonPath('genre', 'rock');
    }

    public function test_finds_bands_by_genre(): void
    {
        $this->getJson('/api/v1/bands/gender/rock')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_creates_band_and_dispatches_index_job(): void
    {
        $this->postJson('/api/v1/bands', [
            'name' => 'Queen',
            'genre' => 'rock',
            'formed_in' => 1970,
        ])
            ->assertCreated()
            ->assertJsonPath('name', 'Queen')
            ->assertHeader('Location', '/api/v1/bands/6');

        $this->assertDatabaseHas('bands', ['name' => 'Queen', 'genre' => 'rock']);
        Bus::assertDispatched(IndexBand::class);
    }

    public function test_unknown_band_returns_problem_details(): void
    {
        $this->getJson('/api/v1/bands/999999')
            ->assertNotFound()
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('code', 'band-not-found')
            ->assertJsonPath('status', 404)
            ->assertJsonStructure(['type', 'title', 'detail', 'instance', 'request_id']);
    }

    public function test_unknown_genre_value_returns_problem_details(): void
    {
        $this->getJson('/api/v1/bands/gender/disco')
            ->assertUnprocessable()
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('code', 'invalid-band-data');
    }

    public function test_valid_genre_without_bands_returns_not_found(): void
    {
        $this->getJson('/api/v1/bands/gender/classical')
            ->assertNotFound()
            ->assertJsonPath('code', 'no-bands-for-gender');
    }

    public function test_invalid_payload_returns_validation_problem(): void
    {
        $this->postJson('/api/v1/bands', [
            'name' => '',
            'genre' => 'unknown',
            'formed_in' => 42,
        ])
            ->assertUnprocessable()
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('code', 'validation-failed')
            ->assertJsonStructure(['errors' => ['name', 'genre', 'formed_in']]);
    }

    public function test_numeric_string_year_is_rejected_as_validation_error(): void
    {
        $this->postJson('/api/v1/bands', [
            'name' => 'Queen',
            'genre' => 'rock',
            'formed_in' => '1970',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'validation-failed')
            ->assertJsonStructure(['errors' => ['formed_in']]);
    }

    public function test_legacy_route_redirects_permanently(): void
    {
        $this->get('/bands')
            ->assertStatus(308)
            ->assertRedirect('/api/v1/bands');
    }

    public function test_request_id_is_echoed_and_propagated_to_job(): void
    {
        $this->withHeader('X-Request-Id', 'trace-42')
            ->getJson('/api/v1/bands/1')
            ->assertOk()
            ->assertHeader('X-Request-Id', 'trace-42');

        $this->postJson('/api/v1/bands', [
            'name' => 'Radiohead',
            'genre' => 'indie',
            'formed_in' => 1985,
        ], ['X-Request-Id' => 'trace-99'])
            ->assertCreated();

        Bus::assertDispatched(IndexBand::class, static fn (IndexBand $job): bool => $job->requestId === 'trace-99');
    }
}
