<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonRequest
{
    /**
     * Force every API request to be treated as JSON.
     *
     * Without an `Accept: application/json` header, Laravel treats an
     * unauthenticated request as a web request and tries to redirect to a
     * non-existent `login` route (500). Forcing the header makes
     * `$request->expectsJson()` true, so auth failures return a clean 401.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
