<?php

declare(strict_types=1);

use Jeemusu\ReqRes\DataTransferObjects\User;
use Jeemusu\ReqRes\DataTransferObjects\UserCollection;
use Jeemusu\ReqRes\Exceptions\ApiException;
use Jeemusu\ReqRes\Exceptions\UserNotFoundException;

describe('UserService', function () {
    describe('getUserById', function () {
        it('returns a valid User DTO on a successful API response', function () {

            $apiResponseData = [
                'data' => [
                    'id' => 3,
                    'email' => 'jameswmorris@gmail.com',
                    'first_name' => 'James',
                    'last_name' => 'Morris',
                    'avatar' => 'https://example.com/img/faces/2-image.jpg'
                ]
            ];

            $userService = $this->createUserServiceWithMockedResponse(200, $apiResponseData);
            $user = $userService->getUserById(2);

            // Assert
            expect($user)->toBeInstanceOf(User::class);
            expect($user->id)->toBe(3);
            expect($user->email)->toBe('jameswmorris@gmail.com');
            expect($user->firstName)->toBe('James');
            expect($user->lastName)->toBe('Morris');
            expect($user->avatar)->toBe('https://example.com/img/faces/2-image.jpg');
        });

        it('throws UserNotFoundException when status code is 404', function () {
            $userService = $this->createUserServiceWithMockedResponse(404, []);

            expect(function () use ($userService) {
                return $userService->getUserById(2);
            })->toThrow(UserNotFoundException::class);
        });

        /*
        it('throws JsonException on malformed json response', function () {
        });

        it('throws NetworkException when there are connection or network issues', function () {
        });
        */

        it('throws ApiException when response missing data field', function () {
            $apiResponseData = [
                'message' => 'Success',
                'status' => 'ok'
            ];

            $userService = $this->createUserServiceWithMockedResponse(200, $apiResponseData);

            expect(function () use ($userService) {
                return $userService->getUserById(2);
            })->toThrow(ApiException::class, 'Invalid response structure: missing or invalid data field');
        });

        it('throws ApiException when response statuscode is 400  (Bad Request)', function () {
            $userService = $this->createUserServiceWithMockedResponse(400, []);

            expect(function () use ($userService) {
                return $userService->getUserById(2);
            })->toThrow(ApiException::class, 'API returned error status 400 for user ID 2');
        });

        it('throws ApiException when response statuscode is 401 (Unauthorized)', function () {
            $userService = $this->createUserServiceWithMockedResponse(401, [], 5);

            expect(function () use ($userService) {
                return $userService->getUserById(5);
            })->toThrow(ApiException::class, 'API returned error status 401 for user ID 5');
        });

        it('throws ApiException when response statuscode is 403 (Forbidden)', function () {
            $userService = $this->createUserServiceWithMockedResponse(403, [], 10);

            expect(function () use ($userService) {
                return $userService->getUserById(10);
            })->toThrow(ApiException::class, 'API returned error status 403 for user ID 10');
        });

        it('throws ApiException when response statuscode is 500 (Internal Server Error)', function () {
            $userService = $this->createUserServiceWithMockedResponse(500, [], 3);

            expect(function () use ($userService) {
                return $userService->getUserById(3);
            })->toThrow(ApiException::class, 'API returned error status 500 for user ID 3');
        });
    });

    describe('getPaginatedUsers', function () {

        it('returns valid UserCollection DTO with default parameters', function () {
            $apiResponseData = [
                'page' => 1,
                'per_page' => 6,
                'total' => 48,
                'total_pages' => 8,
                'data' => [
                    [
                        'id' => 1,
                        'email' => 'jameswmorris@gmail.com',
                        'first_name' => 'James',
                        'last_name' => 'Morris',
                        'avatar' => 'https://example.com/img/faces/1-image.jpg'
                    ],
                    [
                        'id' => 2,
                        'email' => 'janet@example.com',
                        'first_name' => 'Janet',
                        'last_name' => 'Weaver',
                        'avatar' => 'https://example.com/img/faces/2-image.jpg'
                    ]
                ]
            ];

            $userService = $this->createUserServiceWithMockedPaginatedResponse(200, $apiResponseData);
            $userCollection = $userService->getPaginatedUsers();

            // Test the UserCollection
            expect($userCollection)->toBeInstanceOf(UserCollection::class);
            expect($userCollection->page)->toBe(1);
            expect($userCollection->perPage)->toBe(6);
            expect($userCollection->total)->toBe(48);
            expect($userCollection->totalPages)->toBe(8);

            // Test the users data array
            expect($userCollection->users)->toHaveCount(2);
            expect($userCollection->users[0])->toBeInstanceOf(User::class);
            expect($userCollection->users[1])->toBeInstanceOf(User::class);

            // Test first user
            expect($userCollection->users[0]->id)->toBe(1);
            expect($userCollection->users[0]->email)->toBe('jameswmorris@gmail.com');
            expect($userCollection->users[0]->firstName)->toBe('James');
            expect($userCollection->users[0]->lastName)->toBe('Morris');
            expect($userCollection->users[0]->avatar)->toBe('https://example.com/img/faces/1-image.jpg');

            // Test second user
            expect($userCollection->users[1]->id)->toBe(2);
            expect($userCollection->users[1]->email)->toBe('janet@example.com');
            expect($userCollection->users[1]->firstName)->toBe('Janet');
            expect($userCollection->users[1]->lastName)->toBe('Weaver');
            expect($userCollection->users[1]->avatar)->toBe('https://example.com/img/faces/2-image.jpg');
        });

        it('throws ApiException when response statuscode is　400 (Bad Request)', function () {
            $userService = $this->createUserServiceWithMockedPaginatedResponse(400, [], 2, 10);

            expect(function () use ($userService) {
                return $userService->getPaginatedUsers(2, 10);
            })->toThrow(ApiException::class, 'API returned error status 400 for paginated users');
        });

        it('throws ApiException on server error', function () {
            $userService = $this->createUserServiceWithMockedPaginatedResponse(500, []);

            expect(function () use ($userService) {
                return $userService->getPaginatedUsers();
            })->toThrow(ApiException::class, 'API returned error status 500 for paginated users');
        });

        /*
        it('throws JsonException on malformed json response', function () {
            // TODO
        });

        it('throws NetworkException when there are connection or network issues', function () {
            // TODO
        });
        */

        it('throws ApiException when response missing data field', function () {
            $apiResponseData = [
                'page' => 1,
                'per_page' => 6,
                'total' => 48,
                'total_pages' => 8,
            ];

            $userService = $this->createUserServiceWithMockedPaginatedResponse(200, $apiResponseData);

            expect(function () use ($userService) {
                return $userService->getPaginatedUsers();
            })->toThrow(ApiException::class, 'Invalid response structure: missing data field');
        });
    });

    describe('createUser', function () {
        it('creates user successfully when valid parameters are provided and returns an id', function () {

            $apiResponseData = [
                'name' => 'James Morris',
                'job' => 'jameswmorris@gmail.com',
                'id' => '435',
                'createdAt' => '2025-09-16T14:45:23.939Z',
            ];

            $userService = $this->createUserServiceWithMockedCreateResponse(201, $apiResponseData, 'James Morris', 'Fullstack Developer');
            $userId = $userService->createUser('James Morris', 'Fullstack Developer');

            expect($userId)->toBe($apiResponseData['id']);
        });

        it('throws ApiException when response missing id field', function () {
            $apiResponseData = [
                'name' => 'James Morris',
                'job' => 'jameswmorris@gmail.com',
                'createdAt' => '2025-09-16T14:45:23.939Z',
            ];

            $userService = $this->createUserServiceWithMockedCreateResponse(201, $apiResponseData, 'James Morris', 'Fullstack Developer');

            expect(function () use ($userService) {
                return $userService->createUser('James Morris', 'Fullstack Developer');
            })->toThrow(ApiException::class, 'Invalid response structure: missing or invalid id field');
        });

        it('throws ApiException when response statuscode is 400 (Bad Request)', function () {
            $userService = $this->createUserServiceWithMockedCreateResponse(400, [], 'James Morris', 'Fullstack Developer');

            expect(function () use ($userService) {
                return $userService->createUser('James Morris', 'Fullstack Developer');
            })->toThrow(ApiException::class, 'API returned error status 400 when creating user');
        });

        /*
        it('throws JsonException on malformed json response', function () {
            // TODO
        });

        it('throws NetworkException when there are connection or network issues', function () {
            // TODO
        });
        */
    });

    describe('constructor', function () {

        /*
        it('normalizes base URI by adding trailing slash', function () {
            // TODO
        });

        it('handles base URI that already has trailing slash', function () {
            // TODO
        });
        */
    });
});
