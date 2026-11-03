<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTalentLocalAuthEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('talent.local_auth_enabled'), 503, 'Applicant sign-in is not configured for this environment.');

        return $next($request);
    }
}
