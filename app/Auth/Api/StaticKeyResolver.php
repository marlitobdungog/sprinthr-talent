<?php

namespace App\Auth\Api;

class StaticKeyResolver implements ApiCredentialResolver
{
    public function resolve(string $token): ?ApiPrincipal
    {
        foreach (config('talent.api_keys', []) as $key) {
            if (hash_equals($key, $token)) {
                return new ApiPrincipal('service', 'key-'.substr(hash('sha256', $key), 0, 8));
            }
        }

        return null;
    }
}
