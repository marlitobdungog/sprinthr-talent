<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if ($token === null || $token === '') {
            return $this->unauthenticated();
        }

        foreach (config('talent.api_resolvers', []) as $resolver) {
            $principal = app($resolver)->resolve($token);

            if ($principal !== null) {
                $request->attributes->set('api_principal', $principal);

                return $next($request);
            }
        }

        return $this->unauthenticated();
    }

    private function unauthenticated(): Response
    {
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
