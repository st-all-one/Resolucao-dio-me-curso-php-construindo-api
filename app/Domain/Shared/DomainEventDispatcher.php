<?php

declare(strict_types=1);

namespace App\Domain\Shared;

interface DomainEventDispatcher
{
    public function dispatch(DomainEvent $event): void;
}
