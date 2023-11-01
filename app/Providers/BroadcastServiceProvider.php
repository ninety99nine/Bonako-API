<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         *  We are delivberately disabling Broadcast::routes();
         *
         *  This is because this Broadcast::routes(); creates web routes instead of api routes e.g
         *  "broadcasting/auth". We need these routes to be implemented on the api routes e.g
         *  "api/v1/broadcasting/auth". To do this, we simply disable the creation of the
         *  broadcasting web routes from here (comment out Broadcast::routes();) and then
         *  on the "routes/api.php" file, we can implemen the Broadcast::routes(); from
         *  there so that the routes are now implemented on the api layer. This allows
         *  us to authenticate the user on private channels that need authentication
         *  using the authorization bearer token.
         *
         *  Learn more about broadcasting: https://laravel.com/docs/10.x/broadcasting#defining-authorization-routes
         */
        //  Broadcast::routes();

        require base_path('routes/channels.php');
    }
}
