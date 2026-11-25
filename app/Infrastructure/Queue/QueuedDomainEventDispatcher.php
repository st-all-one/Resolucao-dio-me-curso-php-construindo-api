<?php

declare(strict_types=1);

namespace App\Infrastructure\Queue;

use App\Domain\Band\BandCreated;
use App\Domain\Shared\DomainEvent;
use App\Domain\Shared\DomainEventDispatcher;
use Illuminate\Support\Facades\Context;

final readonly class QueuedDomainEventDispatcher implements DomainEventDispatcher
{
    public function dispatch(DomainEvent $event): void
    {
        if (! $event instanceof BandCreated) {
            return;
        }

        /** @var array<string, mixed> $context */
        $context = Context::all();
        $requestId = isset($context['request_id']) && is_scalar($context['request_id'])
            ? (string) $context['request_id']
            : 'cli';

        IndexBand::dispatch(
            bandId: $event->id->value,
            requestId: $requestId,
            context: $this->safeContext($context),
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, string|int|null>
     */
    private function safeContext(array $context): array
    {
        $safe = [];

        foreach ($context as $key => $value) {
            if (is_string($key) && (is_string($value) || is_int($value) || $value === null)) {
                $safe[$key] = $value;
            }
        }

        return $safe;
    }
}
