<?php

namespace App\Auth\Api;

interface ApiCredentialResolver
{
    public function resolve(string $token): ?ApiPrincipal;
}
