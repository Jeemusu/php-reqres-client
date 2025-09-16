<?php

declare(strict_types=1);

namespace Jeemusu\ReqRes\Services;

use Jeemusu\ReqRes\DataTransferObjects\User;
use Jeemusu\ReqRes\DataTransferObjects\UserCollection;
use Jeemusu\ReqRes\Exceptions\ApiException;
use Jeemusu\ReqRes\Exceptions\NetworkException;
use Jeemusu\ReqRes\Exceptions\UserNotFoundException;
use Jeemusu\ReqRes\Interfaces\Services\UserServiceInterface;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class UserService implements UserServiceInterface
{
    /**
     * @var array<string, string|array<string>>
     */
    private array $defaultHeaders;

    /**
     * @var string
     */
    private string $baseUri;

    /**
     * @param ClientInterface $httpClient PSR-18 HTTP client
     * @param RequestFactoryInterface $requestFactory PSR-17 request factory
     * @param non-empty-string $baseUri Base URI ending with or without a slash
     * @param array<string, string|string[]> $defaultHeaders An associative array of default headers to apply to every request.
     */
    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        string $baseUri,
        array $defaultHeaders = []
    ) {
        $this->baseUri = rtrim($baseUri, '/') . '/';
        $this->defaultHeaders = $defaultHeaders;
    }

    /**
     * Retrieves a single user by their ID from the API.
     *
     * This method sends a GET request to the user endpoint and returns a
     * `User` data transfer object.
     *
     * @param int $id The unique integer ID of the user to retrieve.
     * @throws UserNotFoundException If the API responds with a 404 status code.
     * @throws NetworkException If a network-level error occurs (e.g., DNS failure, connection timeout).
     * @throws ApiException For other API-related errors, such as invalid JSON or a 5xx status code.
     * @return User A `User` DTO representing the retrieved user.
     */
    public function getUserById(int $id): User
    {
        try {
            $request = $this->requestFactory->createRequest('GET', "{$this->baseUri}users/{$id}");
            $request = $this->addHeaders($request);

            $response = $this->httpClient->sendRequest($request);

            if ($response->getStatusCode() === 404) {
                throw UserNotFoundException::forUserId($id);
            }

            if ($response->getStatusCode() >= 400) {
                throw new ApiException(
                    "API returned error status {$response->getStatusCode()} for user ID {$id}"
                );
            }

            $data = $this->parseJson($response);

            if (!isset($data['data']) || !is_array($data['data'])) {
                throw new ApiException('Invalid response structure: missing or invalid data field');
            }

            return User::fromArray($data['data']);

        } catch (NetworkExceptionInterface $e) {
            throw NetworkException::connectionFailed("users/{$id}", $e);

        } catch (JsonException $e) {
            throw new ApiException('Failed to parse API response', 0, $e);

        } catch (ClientExceptionInterface $e) {
            throw new ApiException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Retrieves a paginated list of users from the API.
     *
     * This method sends a GET request with pagination parameters and returns a
     * `UserCollection` DTO containing the list of users and pagination metadata.
     *
     * @param int $page The page number to retrieve. Defaults to 1.
     * @param int $perPage The number of users per page. Defaults to 6.
     * @throws NetworkException If a network-level error occurs.
     * @throws ApiException For other API-related errors, such as invalid JSON or a 4xx/5xx status code.
     * @return UserCollection A `UserCollection` DTO containing users and pagination data.
     */
    public function getPaginatedUsers(int $page = 1, int $perPage = 6): UserCollection
    {
        try {
            $uri = $this->baseUri . 'users?' . http_build_query(
                ['page' => $page, 'per_page' => $perPage],
                '',
                '&'
            );

            $request = $this->requestFactory->createRequest('GET', $uri);
            $request = $this->addHeaders($request);
            $response = $this->httpClient->sendRequest($request);

            if ($response->getStatusCode() >= 400) {
                throw new ApiException(
                    "API returned error status {$response->getStatusCode()} for paginated users"
                );
            }

            $data = $this->parseJson($response);

            if (!isset($data['data'])) {
                throw new ApiException('Invalid response structure: missing data field');
            }

            /** @var array{page: int, per_page: int, total: int, total_pages: int, data: list<array{id: int, email: string, first_name: string, last_name: string, avatar: string}>} $data */
            return UserCollection::fromArray($data);

        } catch (NetworkExceptionInterface $e) {
            throw NetworkException::connectionFailed("users", $e);

        } catch (JsonException $e) {
            throw new ApiException('Failed to parse API response', 0, $e);

        } catch (ClientExceptionInterface $e) {
            throw new ApiException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Creates a new user on the API.
     *
     * This method sends a POST request with the user's name and job, and returns
     * the ID of the newly created user. It performs simple validation before
     * sending the request.
     *
     * @param string $name The name of the user to create.
     * @param string $job The job of the user to create.
     * @throws NetworkException If a network-level error occurs.
     * @throws ApiException For other API-related errors, such as an invalid response or a 4xx/5xx status code.
     * @return string The ID of the newly created user.
     */
    public function createUser(string $name, string $job): string
    {
        // Validate parameters here to avoid unnecessary api calls

        try {
            $uri = $this->baseUri . 'users';

            $body = json_encode([
                'name' => trim($name),
                'job'  => trim($job),
            ], JSON_THROW_ON_ERROR);

            $request = $this->requestFactory
                ->createRequest('POST', $uri)
                ->withHeader('Content-Type', 'application/json');

            $request->getBody()->write($body);
            $request = $this->addHeaders($request);

            $response = $this->httpClient->sendRequest($request);

            if ($response->getStatusCode() >= 400) {
                throw new ApiException(
                    "API returned error status {$response->getStatusCode()} when creating user"
                );
            }

            $data = $this->parseJson($response);

            if (!isset($data['id']) || !is_string($data['id'])) {
                throw new ApiException('Invalid response structure: missing or invalid id field');
            }

            return $data['id'];

        } catch (NetworkExceptionInterface $e) {
            throw NetworkException::connectionFailed("users", $e);

        } catch (JsonException $e) {
            throw new ApiException('Failed to parse API response', 0, $e);

        } catch (ClientExceptionInterface $e) {
            throw new ApiException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Adds the default headers to a given HTTP request.
     *
     * @param RequestInterface $request The request instance to which headers will be added.
     * @return RequestInterface The new request instance with default headers.
     */
    private function addHeaders(RequestInterface $request): RequestInterface
    {
        foreach ($this->defaultHeaders as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $request;
    }

    /**
     * Decodes a JSON HTTP response body into an associative array.
     *
     * @param ResponseInterface $response The HTTP response object.
     * @throws JsonException If the JSON is malformed or decoding fails.
     * @return array<string, mixed> The decoded JSON data.
     */
    private function parseJson(ResponseInterface $response): array
    {
        /** @var array<string,mixed> */
        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

}
