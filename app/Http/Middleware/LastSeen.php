<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;

class LastSeen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        //  If the authenticated user exists
        if($request->auth_userExists) {

            /**
             *  @var User $user
             */
            $user = $request->auth_user;

            //  If the last update was at least 5 minutes ago
            if ($user && $user->last_seen_at->diffInMinutes(now()) > 5) {

                //  Update the last seen datetime
                $user->updateLastSeen();

            }

        }

        return $next($request);
    }
}
