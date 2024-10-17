<?php

namespace App\Providers;

use Illuminate\Http\Request;
use App\Services\Ussd\UssdService;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        // Define a rate limiter for general API requests
        RateLimiter::for('api', function (Request $request) {

            // Check if the request is coming from the USSD server
            if (UssdService::verifyIfRequestFromUssdServer()) {

                // Allow more requests per minute if the request is from the USSD server
                return Limit::perMinute(600)->by(optional($request->auth_user)->id ?: $request->ip());

            } else {

                // Allow fewer requests per minute for all other platforms
                return Limit::perMinute(300)->by(optional($request->auth_user)->id ?: $request->ip());

            }

        });

        // Define a rate limiter specifically for Authentication related API requests
        RateLimiter::for('auth', function (Request $request) {

            // More strict rate limiting for auth routes to prevent abuse
            return Limit::perMinute(10)->by(optional($request->auth_user)->id ?: $request->ip());

        });

    }

}
