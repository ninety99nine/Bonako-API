<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\Middleware\Authenticate as Middleware;


class Authenticate extends Middleware
{
   /**
    *  Handle an incoming request.
    *
    *  ---------------------------------------------
    *  handle() method overidden By: Julian B Tabona
    *  ---------------------------------------------
    *
    *   I had to overide the default handle() method so that we can first check
    *   if we have already set an authenticated user. This is possible since we
    *   run the SetAuthUserOnRequest middleware before this Authenticate
    *   middleware. The SetAuthUserOnRequest middleware is responsible to
    *   search for the authenticated user using the Cache and then returning
    *   that authenticated user, otherwise find the user using the bearer
    *   token and then Cache that user as well as set the user as the
    *   authenticated user of the request using the auth()->setUser()
    *   method. Once the auth()->setUser() has been executed, we can
    *   then check if a user was found from the cache system or was
    *   just retrieved directly from the database using the method
    *   auth()->hasUser(). If the user does exist, we know that the
    *   user will now we accessible to the rest of the application
    *   by simply using:
    *
    *   "auth()->user()" or "request()->user()" or "request()->auth_user"
    *
    *   On this handle method, we are just simply checking if the user was
    *   found before we proceed with running queries to authenticate the
    *   user before proceeding with the rest of the application. The
    *   Authenticate middleware is still important eventhough we can
    *   find a user using the SetAuthUserOnRequest method. This
    *   is because the SetAuthUserOnRequest will always fetch
    *   the user but will never throw an exception if the user
    *   token is incorrect.
    *
    *   We will throw an exception only when the Authenticate middleware is
    *   applied since its not every route that requires an authenticated
    *   user to be present in order to perform the necessary action.
    *
    *   In this way the SetAuthUserOnRequest middleware will focus on
    *   getting the authenticated user from the cache and setting that
    *   authenticated user on application auth() instance and the
    *   request "auth_user" property. The Authenticate middleware
    *   will focus on throwing exceptions if the request requires
    *   any authenticated user but non is found.
    *
    *   Refer to the following files to understand how Laravel authentication
    *   and Laravel sanctum authentication works:
    *
    *   Illuminate\Auth\AuthServiceProvider
    *   Illuminate\Auth\AuthManager
    *   Illuminate\Auth\Middleware\Authenticate
    *   Illuminate\Auth\RequestGuard
    *   Illuminate\Auth\GuardHelpers
    *   Laravel\Sanctum\SanctumServiceProvider
    *   Laravel\Sanctum\Guard
    *   Laravel\Sanctum\HasApiTokens
    *
    *   @param  \Illuminate\Http\Request  $request
    *   @param  \Closure  $next
    *   @param  string[]  ...$guards
    *   @return mixed
    *
    *   @throws \Illuminate\Auth\AuthenticationException
    */
   public function handle($request, Closure $next, ...$guards)
   {
        /**
         *  --------------------------------------
         *  handle() overidden By: Julian B Tabona
         *  --------------------------------------
         *  If request()->auth_user_exists = true but its value is set to false, then we know that the
         *  SetAuthUserOnRequest middleware has attempted to find the user using the provided
         *  bearer token, but failed. We do not have to run parent::authenticate() since this will
         *  make an additional request to find the user using the same bearer token but only to
         *  fail. We can just simply run parent::unauthenticated() to fail this request. This
         *  way we save making additional queries to the database when yet we alreadyknow the
         *  outcome.
         */
        if( isset(request()->auth_user_exists) && request()->auth_user_exists == false ) {

            parent::unauthenticated($request, $guards);

        }else if( !isset(request()->auth_user_exists) ) {

            //  Continue the authentication process
            parent::authenticate($request, $guards);

        }

        return $next($request);
   }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): string|null
    {
        /**
         *  Since we are designing an API, we do not want to redirect to the
         *  login page when we are not authenticated especially incase a
         *  developer forgets to provide the following headers:
         *
         *  Accept: application/json
         *  Content-Type: application/json
         *
         *  This redirect is a normal behaviour by Laravel when designing non SPA
         *  applications but is not desired for our use case.
         */
        if ( $request->expectsJson() ) {

            //  Do not redirect on API Request
            return null;

        }else{

            //  Do not redirect on non API Request
            return null;    //  return route('login');

        }
    }
}
