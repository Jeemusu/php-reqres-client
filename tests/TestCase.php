<?php

namespace Jeemusu\ReqRes\Tests;

use Jeemusu\ReqRes\Services\UserService;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

abstract class TestCase extends BaseTestCase
{
    protected function createMockHttpClient(): ClientInterface
    {
        return $this->createMock(ClientInterface::class);
    }

    protected function createMockRequestFactory(): RequestFactoryInterface
    {
        return $this->createMock(RequestFactoryInterface::class);
    }

    protected function createMockRequest(): RequestInterface
    {
        return $this->createMock(RequestInterface::class);
    }

    protected function createMockResponse(int $statusCode = 200, string $body = ''): ResponseInterface
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getBody')->willReturn($stream);

        return $response;
    }

    protected function createUserServiceWithMockedResponse(int $statusCode, array $responseData, int $userId = 2): UserService
    {
        $httpClient = test()->createMockHttpClient();
        $requestFactory = test()->createMockRequestFactory();
        $request = test()->createMockRequest();
        $response = test()->createMockResponse($statusCode, json_encode($responseData));

        $requestFactory
            ->expects(test()->once())
            ->method('createRequest')
            ->with('GET', "https://reqres.in/api/users/{$userId}")
            ->willReturn($request);

        $httpClient
            ->expects(test()->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        return new UserService($httpClient, $requestFactory, 'https://reqres.in/api/');
    }

    protected function createUserServiceWithMockedPaginatedResponse(int $statusCode, array $responseData, int $page = 1, int $perPage = 6): UserService
    {
        $httpClient = test()->createMockHttpClient();
        $requestFactory = test()->createMockRequestFactory();
        $request = test()->createMockRequest();
        $response = test()->createMockResponse($statusCode, json_encode($responseData));

        $requestFactory
            ->expects(test()->once())
            ->method('createRequest')
            ->with('GET', "https://reqres.in/api/users?page={$page}&per_page={$perPage}")
            ->willReturn($request);

        $httpClient
            ->expects(test()->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        return new UserService($httpClient, $requestFactory, 'https://reqres.in/api/');
    }

    protected function createUserServiceWithMockedCreateResponse(int $statusCode, array $responseData, string $name, string $job): UserService
    {
        $httpClient = test()->createMockHttpClient();
        $requestFactory = test()->createMockRequestFactory();
        $request = test()->createMockRequest();
        $response = test()->createMockResponse($statusCode, json_encode($responseData));

        $requestFactory
            ->expects(test()->once())
            ->method('createRequest')
            ->with('POST', 'https://reqres.in/api/users')
            ->willReturn($request);

        $request
            ->expects(test()->once())
            ->method('withHeader')
            ->with('Content-Type', 'application/json')
            ->willReturnSelf();

        $mockBody = test()->createMock(StreamInterface::class);

        $request
            ->expects(test()->once())
            ->method('getBody')
            ->willReturn($mockBody);

        $mockBody
            ->expects(test()->once())
            ->method('write')
            ->with(json_encode(['name' => $name, 'job' => $job]));

        $httpClient
            ->expects(test()->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        return new UserService($httpClient, $requestFactory, 'https://reqres.in/api/');
    }
}
