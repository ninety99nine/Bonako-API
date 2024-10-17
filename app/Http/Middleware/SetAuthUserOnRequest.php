<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\RequestAuthUser;

class SetAuthUserOnRequest
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
        /**
         *  Performance Measure:
         *
         *  Note that everytime that we call the auth()->user() or $request->user() to capture
         *  the authenticated user, we innevitably query the database to pull the user based
         *  on the provided bearer token of the API request. This means that on every request
         *  and at various parts of the application that we call auth()->user() we make a
         *  query each time making our requests much slower. Imagine a scenario where a
         *  single request could have a total of auth()->user() being called in various
         *  methods. This means that each request would result in 20 queries just to
         *  fetch the authenticated user alone. To mitigate this performance issue,
         *  we can query the auth user once and set the return value on the current
         *  request. We can also cache the result to reduce the number of queries
         *  made by using valid tokens by making use of the Cache driver
         *
         *  The next middleware after this is the "Authenticate" middleware. Refer
         *  to the protected $middlewarePriority = [] of the kernel.php file. This
         *  shows that after we run the SetAuthUserOnRequest, then we can run
         *  the Authenticate middleware. Notice that we overide the handle method
         *  of the Authenticate middleware so that we can compliment the logic
         *  that was executed by the setAuthUserOnRequest() method.
         */
        (new RequestAuthUser)->setAuthUserOnRequest();

        return $next($request);
    }
}
