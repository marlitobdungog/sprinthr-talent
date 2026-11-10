<?php

namespace App\Auth\Api;

final class ApiPrincipal
{
    public function __construct(
        public readonly string $type,
        public readonly string $id,
    ) {}

    public function allows(string $ability): bool
    {
        return $this->type === 'service';
    }
}
