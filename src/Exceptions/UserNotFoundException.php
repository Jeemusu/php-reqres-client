<?php

declare(strict_types=1);

namespace Jeemusu\ReqRes\Exceptions;

use Throwable;

final class UserNotFoundException extends ApiException
{
    /**
     * Creates a new exception for a user not found by their ID.
     *
     * @param int $id The ID of the user that was not found.
     * @param Throwable|null $previous The previous exception used for chaining.
     * @return self
     */
    public static function forUserId(int $id, ?Throwable $previous = null): self
    {
        return new self(
            message: "User with ID {$id} was not found",
            code: 404,
            previous: $previous
        );
    }
}
