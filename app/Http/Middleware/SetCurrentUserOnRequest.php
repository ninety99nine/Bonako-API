<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;

class SetCurrentUserOnRequest
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->route('user')) {

            // If the route has a 'user' parameter, use that user
            $request->merge(['current_user' => User::find($request->route('user'))]);

        } else {

            // Otherwise, use the authenticated user
            $request->merge(['current_user' => $request->auth_user]);

        }

        return $next($request);
    }
}
