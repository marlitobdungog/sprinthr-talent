<?php

use App\Auth\Api\StaticKeyResolver;

return [
    'consent_policy_version' => env('TALENT_CONSENT_POLICY_VERSION', 'draft-1'),
    'local_auth_enabled' => env('TALENT_LOCAL_AUTH_ENABLED', true),
    'privacy_policy_url' => env('TALENT_PRIVACY_POLICY_URL', 'https://sprinthr.com/legal/privacy-policy'),
    'privacy_policy_version' => env('TALENT_PRIVACY_POLICY_VERSION', 'sprinthr-2026-10-01'),
    'resume_disk' => env('TALENT_RESUME_DISK', 'bucket-api-r2'),
    'api_keys' => array_values(array_filter(array_map('trim', explode(',', (string) env('TALENT_API_KEY', ''))))),
    'api_resolvers' => [
        StaticKeyResolver::class,
    ],
];
