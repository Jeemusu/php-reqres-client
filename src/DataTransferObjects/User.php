<?php

declare(strict_types=1);

namespace Jeemusu\ReqRes\DataTransferObjects;

use JsonSerializable;

/**
 * @phpstan-type UserArray array{
 *   id: int,
 *   email: string,
 *   first_name: string,
 *   last_name: string,
 *   avatar: string
 * }
 */
final class User implements JsonSerializable
{
    public function __construct(
        public readonly int $id,
        public readonly string $email,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $avatar,
    ) {
    }

    /**
     * Converts a User to an array.
     *
     * @return UserArray
    */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'avatar' => $this->avatar,
        ];
    }

    /**
     * Create User from an array.
     *
     * @param UserArray $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            email: $data['email'],
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            avatar: $data['avatar']
        );
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
