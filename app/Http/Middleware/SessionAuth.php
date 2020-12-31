<?php

namespace App\Http\Middleware;

use App\Models\AppSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class SessionAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Get the session token cookie
        $session_id = Cookie::get(config('custom.cookie_session_token'));
        if ($session_id == null)
        {
            abort(403);
        }

        // Get the session from the DB
        $session = AppSession::getSession($session_id);
        if($session == null)
        {
            abort(403);
        }

        AppSession::authenticate($session->user_id);
        return $next($request);
    }
}
