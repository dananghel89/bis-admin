<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMembershipIsOpen
{
    /**
     * Send visitors back to the front page when registration is closed.
     *
     * The "membership" option is read per request, so changing it in
     * Dashboard › Options takes effect at once, even with cached routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! get_option('membership')) {
            return redirect()->route('front');
        }

        return $next($request);
    }
}
