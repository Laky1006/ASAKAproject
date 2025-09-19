<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // allow only logged-in users with role = 'admin'
        if ($request->user() && $request->user()->role === 'admin') {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}
