<?php

declare(strict_types=1);

namespace App\Infrastructure\Queue;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;

/**
 * Efeito assíncrono da criação de uma banda: indexação auditável.
 * O payload carrega apenas escalares + request_id — sem Eloquent, sem Model.
 */
#[Tries(3)]
#[Backoff([5, 15, 30])]
#[Timeout(30)]
final class IndexBand implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    /**
     * @param  array<string, string|int|null>  $context
     */
    public function __construct(
        public readonly int $bandId,
        public readonly string $requestId,
        public readonly array $context = [],
    ) {}

    public function handle(): void
    {
        Context::add('request_id', $this->requestId);
        Context::add('job', self::class);

        foreach ($this->context as $key => $value) {
            if ($value !== null) {
                Context::add($key, $value);
            }
        }

        Log::info('band.indexed', ['band_id' => $this->bandId]);
    }
}
