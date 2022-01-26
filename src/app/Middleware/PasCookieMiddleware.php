<?php

namespace Different\PatentOAuthClient\app\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class PasCookieMiddleware
{
    public function handle($request, Closure $next)
    {
        // Ha nem létezik a cookie...
        if (!$request->hasCookie('pas_logged_in_from')) {
            $user = Auth::user();
            if (isset($user) && !empty($user)) {
                // de valamiért létezik lokális felhasználó / login akkor jelentkeztessük ki.
                return redirect(backpack_url('logout'));
            }
        }
        
        return $next($request);
    }
}
