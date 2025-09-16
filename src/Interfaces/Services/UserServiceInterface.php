<?php

declare(strict_types=1);

namespace Jeemusu\ReqRes\Interfaces\Services;

use Jeemusu\ReqRes\DataTransferObjects\User;
use Jeemusu\ReqRes\DataTransferObjects\UserCollection;
use Jeemusu\ReqRes\Exceptions\ApiException;
use Jeemusu\ReqRes\Exceptions\NetworkException;
use Jeemusu\ReqRes\Exceptions\UserNotFoundException;

interface UserServiceInterface
{
    /**
     * Retrieves a single user by their unique ID.
     *
     * @param int $id The unique integer ID of the user.
     * @throws UserNotFoundException If a user with the given ID is not found.
     * @throws NetworkException If a network error prevents the request from completing.
     * @throws ApiException For any other API-related errors.
     * @return User The User data transfer object.
     */
    public function getUserById(int $id): User;

    /**
     * Retrieves a paginated list of users.
     *
     * @param int $page The page number to retrieve.
     * @throws NetworkException If a network error prevents the request from completing.
     * @throws ApiException For any other API-related errors.
     * @param int $perPage The number of users per page.
     */
    public function getPaginatedUsers(int $page = 1, int $perPage = 6): UserCollection;

    /**
     * Creates a new user with the provided name and job.
     *
     * @param string $name The name of the user.
     * @param string $job The job title of the user.
     * @throws NetworkException If a network error prevents the request from completing.
     * @throws ApiException For any other API-related errors.
     * @return string The ID of the newly created user.
     */
    public function createUser(string $name, string $job): string;
}
