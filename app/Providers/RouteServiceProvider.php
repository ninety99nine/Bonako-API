<?php

namespace App\Providers;

use Exception;
use App\Models\User;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });

        /**
         *  Explicitly define how the store route parameter is resolved.
         *  Reference: https://laravel.com/docs/10.x/routing#customizing-the-resolution-logic
         */
        Route::bind('store', function (string $storeId) {

            /**
             *  @var User $user
             */
            $user = auth()->user();

            /**
             *  First search for this store through the user and store
             *  association relationship. This allows us to load the
             *  user_store_association pivot relationship to better
             *  understand how the user is associated with this
             *  store e.g is the user a follower, customer,
             *  team member e.t.c
             */
            $store = $user->stores()->where('stores.id', $storeId)->first();

            /**
             *  If the store could not be retireved
             *  via the user and store association
             */
            if(is_null($store)) {

                //  Acquire the store directly otherwise fail
                $store = Store::findOrFail($storeId);

            }

            return $store;

        });

        /**
         *  Explicitly define how the order route parameter is resolved.
         *  Reference: https://laravel.com/docs/10.x/routing#customizing-the-resolution-logic
         */
        Route::bind('order', function (string $orderId) {

            /**
             *  @var User $user
             */
            $user = auth()->user();

            /**
             *  @var Store $store
             */
            $store = request()->store;

            //  If the store could not be retireved
            if(is_null($store)) {

                //  Throw an exception to indicate that the store must be resolved first
                new Exception('The store is required in order to resolve this order while using route model binding. Make sure that the route resolves the store first before attempting to resolve this order e.g show(Store $store, Order $order){ ... }', 400);

            }

            //  Get the user and order association if the user is assigned to this order
            $userOrderCollectionAssociation = DB::table('user_order_collection_association')
                                        ->where('order_id', $orderId)
                                        ->where('user_id', $user->id)
                                        ->first();

            //  If the user is associated with this order in any way e.g customer or friend
            if($userOrderCollectionAssociation) {

                /**
                 *  Get the order based on the user association to this order so
                 *  that we can load the user_order_collection_association pivot
                 *  table
                 */
                $order = $user->orders()
                            ->where('orders.store_id', $store->id)
                            ->where('orders.id', $orderId)
                            ->first();
            }

            /**
             *  If the order could not be retireved via the
             *  user and order collection association
             */
            if(!isset($order)) {

                //  Acquire the order directly from the store otherwise fail
                $order = $store->orders()->where('orders.id', $orderId)->firstOrFail();

            }

            return $order;

        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            /**
             *  Julian B Tabona
             *
             *  I'm changing the rate limit from "120" to "240" (double)
             *  so that we can do more requests on the various platforms
             */
            return Limit::perMinute(240)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
