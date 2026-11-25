<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Correlaciona request → logs → fila. O id do cliente é aceito somente quando
 * cabe em 64 caracteres alfanuméricos; caso contrário um novo é gerado.
 */
final class AssignRequestId
{
    public function handle(Request $request, \Closure $next): Response
    {
        $provided = $request->header('X-Request-Id');
        $requestId = is_string($provided) && preg_match('/\A[A-Za-z0-9._-]{1,64}\z/', $provided) === 1
            ? $provided
            : (string) Str::uuid();

        Context::add('request_id', $requestId);
        Context::add('route', $request->route()?->getName() ?? 'unmatched');

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}
