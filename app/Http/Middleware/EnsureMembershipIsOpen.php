<?php

namespace App\Http\Middleware;

use Closure;

class EnsureMembershipIsOpen
{
    /**
     * Send visitors back to the front page when registration is closed.
     *
     * The "membership" option is read per request, so changing it in
     * Dashboard › Options takes effect at once, even with cached routes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! get_option('membership')) {
            return redirect()->route('front');
        }

        return $next($request);
    }
}
