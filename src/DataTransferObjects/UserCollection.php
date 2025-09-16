<?php

declare(strict_types=1);

namespace Jeemusu\ReqRes\DataTransferObjects;

use JsonSerializable;

/**
 * @phpstan-import-type UserArray from User
 *
 * @phpstan-type UserCollectionArray array{
 *     page: int,
 *     per_page: int,
 *     total: int,
 *     total_pages: int,
 *     data: list<UserArray>
 * }
 */
final class UserCollection implements JsonSerializable
{
    /** @param list<User> $users */
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly int $total,
        public readonly int $totalPages,
        public readonly array $users,
    ) {
    }

    /**
     * Converts a UserCollection to an array.
     *
     * @return UserCollectionArray
    */
    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'per_page' => $this->perPage,
            'total' => $this->total,
            'total_pages' => $this->totalPages,
            'data' => array_map(function (User $user) {
                return $user->toArray();
            }, $this->users),
        ];
    }

    /**
     * Create a UserCollection from an array.
     *
     * @param UserCollectionArray $data
     */
    public static function fromArray(array $data): self
    {
        $users = array_map(
            static fn (array $userData): User => User::fromArray($userData),
            $data['data']
        );

        return new self(
            page: $data['page'],
            perPage: $data['per_page'],
            total: $data['total'],
            totalPages: $data['total_pages'],
            users: $users
        );
    }

    /** @return UserCollectionArray */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
