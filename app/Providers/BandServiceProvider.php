<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Band\BandRepository;
use App\Domain\Shared\DomainEventDispatcher;
use App\Infrastructure\Band\EloquentBandMapper;
use App\Infrastructure\Band\EloquentBandRepository;
use App\Infrastructure\Queue\IndexBand;
use App\Infrastructure\Queue\QueuedDomainEventDispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

final class BandServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EloquentBandMapper::class, static fn (): EloquentBandMapper => new EloquentBandMapper);
        $this->app->singleton(BandRepository::class, static fn (Application $app): BandRepository => new EloquentBandRepository(
            $app->make(EloquentBandMapper::class),
        ));
        $this->app->singleton(DomainEventDispatcher::class, static fn (): DomainEventDispatcher => new QueuedDomainEventDispatcher);

    }

    public function boot(): void
    {
        Queue::route(IndexBand::class, queue: 'indexing');

        Queue::failing(static function (JobFailed $event): void {
            Log::error('job.failed', [
                'job' => $event->job->resolveName(),
                'connection' => $event->connectionName,
                'queue' => $event->job->getQueue(),
            ]);
        });

        Queue::after(static function (JobProcessed $event): void {
            Context::add('job', $event->job->resolveName());
            Log::info('job.processed', [
                'job' => $event->job->resolveName(),
                'connection' => $event->connectionName,
                'queue' => $event->job->getQueue(),
            ]);
        });
    }
}
