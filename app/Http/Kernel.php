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
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
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
        'require.api.headers' => \App\Http\Middleware\RequireApiHeaders::class,
        'accepted.terms.and.conditions' => \App\Http\Middleware\AcceptedTermsAndConditions::class,
        'store.permission' => \App\Http\Middleware\CheckIfHasStorePermissions::class,
        'assigned.to.store.as.team.member' => \App\Http\Middleware\CheckIfAssignedToStoreAsTeamMember::class,
        'superadmin' => \App\Http\Middleware\CheckIfSuperAdmin::class,
        'format.request.and.response.payloads' => \App\Http\Middleware\FormatRequestAndResponsePayload::class,
        'last.seen' => \App\Http\Middleware\LastSeen::class,
        'last.seen.at.store' => \App\Http\Middleware\LastSeenAtStore::class,
        'mark.order.as.seen.by.team.member' => \App\Http\Middleware\MarkOrderAsSeenByTeamMember::class,
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
        \App\Http\Middleware\RequireApiHeaders::class,  //  Run this first
        \App\Http\Middleware\Authenticate::class,       //  Then this (Authentication)

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
