<?php

declare(strict_types=1);

namespace Jeemusu\ReqRes\DataTransferObjects;

use JsonSerializable;

final class UserCollection implements JsonSerializable
{
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly int $total,
        public readonly int $totalPages,
        public readonly array $users,
    ) {
    }

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

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
