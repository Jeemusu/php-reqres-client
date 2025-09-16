<?php

declare(strict_types=1);

namespace Jeemusu\ReqRes\Services;

use Jeemusu\ReqRes\DataTransferObjects\User;
use Jeemusu\ReqRes\DataTransferObjects\UserCollection;
use Jeemusu\ReqRes\Exceptions\ApiException;
use Jeemusu\ReqRes\Interfaces\Services\UserServiceInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

final class UserService implements UserServiceInterface
{
    private array $defaultHeaders;

    private string $baseUri;

    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        string $baseUri,
        array $defaultHeaders = []
    ) {
        $this->baseUri = rtrim($baseUri, '/') . '/';
        $this->defaultHeaders = $defaultHeaders;
    }

    public function getUserById(int $id): User
    {
        try {
            $request = $this->requestFactory->createRequest('GET', "{$this->baseUri}users/{$id}");
            $request = $this->addHeaders($request);

            $response = $this->httpClient->sendRequest($request);

            $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            if (!isset($data['data']) || !is_array($data['data'])) {
                throw new ApiException('Invalid response structure: missing or invalid data field');
            }

            return User::fromArray($data['data']);

        } catch (\Exception $e) {
            throw new ApiException(
                "API returned error status {$response->getStatusCode()} for paginated users"
            );
        }
    }

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

            $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            return UserCollection::fromArray($data);

        } catch (\Exception $e) {
            throw new ApiException(
                "API returned error status {$response->getStatusCode()} for paginated users"
            );
        }
    }

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

            $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            return $data['id'];

        } catch (\Exception $e) {
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


}
