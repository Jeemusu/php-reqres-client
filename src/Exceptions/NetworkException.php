<?php

declare(strict_types=1);

namespace Jeemusu\ReqRes\Exceptions;

use Throwable;

final class NetworkException extends ApiException
{
    /**
     * Creates a new exception for a failed API connection.
     *
     * @param string $endpoint The API endpoint that the connection failed for.
     * @param Throwable|null $previous The previous exception used for the exception chaining.
     * @return self
     */
    public static function connectionFailed(string $endpoint, ?Throwable $previous = null): self
    {
        return new self(
            message: "Failed to connect to API endpoint: {$endpoint}",
            code: 0,
            previous: $previous,
            endpoint: $endpoint
        );
    }
}
