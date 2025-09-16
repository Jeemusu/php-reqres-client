<?php

declare(strict_types=1);

namespace Jeemusu\ReqRes\Exceptions;

use RuntimeException;
use Throwable;

class ApiException extends RuntimeException
{
    /**
     * @param string $message The exception message.
     * @param int $code The exception code.
     * @param Throwable|null $previous The previous exception for chaining.
     * @param string|null $endpoint The API endpoint that was called.
     * @param array|null $requestData The data sent with the request.
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null,
        private readonly ?string $endpoint = null,
        private readonly ?array $requestData = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the API endpoint related to the exception.
     *
     * @return string|null
     */
    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    /**
     * Get the request data related to the exception.
     *
     * @return array|null
     */
    public function getRequestData(): ?array
    {
        return $this->requestData;
    }
}
