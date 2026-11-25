<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Band\Band;
use App\Domain\Band\BandId;
use App\Domain\Band\BandRepository;
use App\Domain\Band\Genre;
use App\Infrastructure\Http\Problem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(Problem::class)]
final class ProblemDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_unexpected_api_error_is_problem_details_without_stack_trace(): void
    {
        $this->instance(BandRepository::class, new class implements BandRepository
        {
            public function all(int $limit, int $offset): array
            {
                throw new \RuntimeException('segredo que nao deve vazar');
            }

            public function count(): int
            {
                return 0;
            }

            public function findById(BandId $id): ?Band
            {
                return null;
            }

            public function findByGenre(Genre $genre, int $limit, int $offset): array
            {
                return [];
            }

            public function countByGenre(Genre $genre): int
            {
                return 0;
            }

            public function save(Band $band): Band
            {
                return $band;
            }
        });

        $response = $this->getJson('/api/v1/bands');

        $response->assertStatus(500)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('code', 'internal-error')
            ->assertJsonPath('detail', 'An unexpected error occurred.');

    }
}
