<?php

declare(strict_types=1);

namespace Jeemusu\ReqRes\DataTransferObjects;

use JsonSerializable;

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
     * @return array
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
     * @param array $data
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

    /** @return array */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
