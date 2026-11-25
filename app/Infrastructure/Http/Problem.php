<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Context;

final class Problem
{
    /**
     * @param  array<string, list<string>>  $errors
     */
    public static function response(
        int $status,
        string $code,
        string $title,
        string $detail,
        string $instance,
        array $errors = [],
    ): JsonResponse {
        $requestId = Context::get('request_id');

        $body = [
            'type' => 'https://httpstatuses.io/' . $status,
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
            'instance' => $instance,
            'code' => $code,
            'request_id' => is_string($requestId) ? $requestId : 'cli',
        ];

        if ($errors !== []) {
            $body['errors'] = $errors;
        }

        return new JsonResponse(
            data: $body,
            status: $status,
            headers: ['Content-Type' => 'application/problem+json'],
        );
    }
}
