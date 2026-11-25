<?php

declare(strict_types=1);

use App\Domain\Band\BandNotFound;
use App\Domain\Band\InvalidBandData;
use App\Infrastructure\Http\Middleware\AssignRequestId;
use App\Infrastructure\Http\Problem;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: static function (): void {
            Route::middleware('api')->group(__DIR__ . '/../routes/dashboard.php');
            require __DIR__ . '/../routes/legacy.php';
        },
    )
    ->withMiddleware(static function (Middleware $middleware): void {
        $middleware->append(AssignRequestId::class);
    })
    ->withExceptions(static function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            static fn (Request $request): bool => $request->is('api', 'api/*', 'bands', 'bands/*')
                || $request->expectsJson(),
        );

        $exceptions->render(static function (ValidationException $exception, Request $request): ?JsonResponse {
            if (! $request->is('api', 'api/*', 'bands', 'bands/*')) {
                return null;
            }

            $errors = [];

            foreach ($exception->errors() as $field => $messages) {
                if (! is_string($field) || ! is_array($messages)) {
                    continue;
                }

                $errors[$field] = array_values(array_map(
                    static fn (mixed $message): string => is_string($message) ? $message : 'Invalid value.',
                    $messages,
                ));
            }

            return Problem::response(
                status: 422,
                code: 'validation-failed',
                title: 'Unprocessable Content',
                detail: 'The request payload contains invalid fields.',
                instance: $request->getPathInfo(),
                errors: $errors,
            );
        });

        $exceptions->render(static function (BandNotFound $exception, Request $request): ?JsonResponse {
            if (! $request->is('api', 'api/*', 'bands', 'bands/*')) {
                return null;
            }

            return Problem::response(
                status: 404,
                code: str_contains($exception->getMessage(), 'genre') ? 'no-bands-for-gender' : 'band-not-found',
                title: 'Not Found',
                detail: $exception->getMessage(),
                instance: $request->getPathInfo(),
            );
        });

        $exceptions->render(static function (InvalidBandData $exception, Request $request): ?JsonResponse {
            if (! $request->is('api', 'api/*', 'bands', 'bands/*')) {
                return null;
            }

            return Problem::response(
                status: 422,
                code: 'invalid-band-data',
                title: 'Unprocessable Content',
                detail: $exception->getMessage(),
                instance: $request->getPathInfo(),
            );
        });

        $exceptions->render(static function (HttpExceptionInterface $exception, Request $request): ?JsonResponse {
            if (! $request->is('api', 'api/*', 'bands', 'bands/*')) {
                return null;
            }

            $status = $exception->getStatusCode();
            $title = Response::$statusTexts[$status] ?? 'Error';

            return Problem::response(
                status: $status,
                code: $status === 404 ? 'route-not-found' : 'http-error',
                title: $title,
                detail: $status === 500 ? 'An unexpected error occurred.' : $exception->getMessage(),
                instance: $request->getPathInfo(),
            );
        });

        $exceptions->render(static function (Throwable $exception, Request $request): ?JsonResponse {
            if (! $request->is('api', 'api/*', 'bands', 'bands/*')) {
                return null;
            }

            return Problem::response(
                status: 500,
                code: 'internal-error',
                title: 'Internal Server Error',
                detail: 'An unexpected error occurred.',
                instance: $request->getPathInfo(),
            );
        });
    })->create();
