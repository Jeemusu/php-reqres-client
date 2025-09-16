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

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
