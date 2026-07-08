<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the /api/* sync endpoints used by the local (producer) KitabAI
 * instance. Not a general-purpose auth system — just a shared secret so
 * randos can't push fake books or vacuum up pending requests.
 */
class VerifySyncToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.sync.token');
        $provided = (string) $request->bearerToken();

        if (blank($expected) || blank($provided) || ! hash_equals($expected, $provided)) {
            abort(401, 'Token sync tidak valid.');
        }

        return $next($request);
    }
}
