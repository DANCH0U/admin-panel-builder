<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user) {
            if ($request->expectsJson()) {
                abort(401, 'Unauthorized');
            }

            return redirect()->guest(route('login'));
        }

        if (! $user->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
