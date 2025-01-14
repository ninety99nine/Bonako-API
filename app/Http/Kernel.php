<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            //  \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     *
     * Aliases may be used instead of class names to conveniently assign middleware to routes and groups.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        /**
         *  Custom Middleware -  By Julian B Tabona
         */
        'request.via.ussd' => \App\Http\Middleware\CheckIfRequestFromUssdServer::class,
        'store.permission' => \App\Http\Middleware\CheckIfHasStorePermissions::class,
        'superadmin' => \App\Http\Middleware\CheckIfSuperAdmin::class,
        'format.request.payload' => \App\Http\Middleware\FormatRequestPayload::class,
        'format.response.payload' => \App\Http\Middleware\FormatResponsePayload::class,
        'response.payload.limiter' => \App\Http\Middleware\ResponsePayloadLimiter::class,
        'last.seen' => \App\Http\Middleware\LastSeen::class,
        'mark.order.as.seen.by.team.member' => \App\Http\Middleware\MarkOrderAsSeenByTeamMember::class,
        'set.auth.user.on.request' => \App\Http\Middleware\SetAuthUserOnRequest::class,
        'set.current.user.on.request' => \App\Http\Middleware\SetCurrentUserOnRequest::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var string[]
     */
    protected $middlewarePriority = [

        /**
         *  Prioritize checking if the developer has provided the important headers before
         *  consuming our API. It's crucial to perform this check before verifying if the
         *  user is authenticated. By modifying the default behavior were laravel checks
         *  if the user is authenticated first, we ensure that the header validation
         *  takes precedence over user authentication.
         */
        \App\Http\Middleware\RequireApiHeaders::class,                  //  Require API headers first
        \App\Http\Middleware\FormatRequestPayload::class,               //  Then format the request payload
        \App\Http\Middleware\SetAuthUserOnRequest::class,               //  Then set the authenticated user on request (if any)
        \App\Http\Middleware\SetCurrentUserOnRequest::class,            //  Then set the current user on request (if any)
        \App\Http\Middleware\Authenticate::class,                       //  Then Authenticate the request
        \Illuminate\Routing\Middleware\ThrottleRequests::class,         //  Then check the rate limits against the authenticated user (if any)

        /**
         *  Remember that the middlewares are fired one after another in linear diretion.
         *  First the middlewares are executed from the first until the last when the request enters,
         *  and then we run the middlewares in reverse order, that is from the last to the first when
         *  we are sending out a response. Since the ResponsePayloadLimiter and the FormatResponsePayload
         *  are executed when the response is being sent out, we should place the FormatResponsePayload
         *  after the ResponsePayloadLimiter, so that when the response is returned, we execute the
         *  FormatResponsePayload and then the ResponsePayloadLimiter.
         */
        \App\Http\Middleware\ResponsePayloadLimiter::class,             //  Then limit the response payload
        \App\Http\Middleware\FormatResponsePayload::class,              //  Then format the response payload

        /**
         *  Always run the route model binding after the user is authenticated. This ensures
         *  that we can access the authenticated user when defining explicit route model
         *  binding using the `boot()` method of the RouteServiceProvider. By doing this,
         *  we can utilize the authenticated user for various purposes. If the route
         *  model binding is performed before the user is authenticated, then the
         *  route model bindings of the RouteServiceProvider `boot()` method will
         *  return `null` when attempting to access auth()->user().
         */
        \Illuminate\Routing\Middleware\SubstituteBindings::class, // Then this (Route model binding)

    ];
}
