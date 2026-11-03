<?php

return [
    'consent_policy_version' => env('TALENT_CONSENT_POLICY_VERSION', 'draft-1'),
    'local_auth_enabled' => env('TALENT_LOCAL_AUTH_ENABLED', env('APP_ENV') === 'local'),
    'resume_disk' => env('TALENT_RESUME_DISK', 'local'),
];
