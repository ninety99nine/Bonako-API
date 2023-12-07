<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\ShoppingCart\ShoppingCartService;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'user' => 'App\Models\User',
            'order' => 'App\Models\Order',
            'store' => 'App\Models\Store',
            'sms alert' => 'App\Models\SmsAlert',
            'transaction' => 'App\Models\Transaction',
            'ai assistant' => 'App\Models\AiAssistant',
            'subscription' => 'App\Models\Subscription',
        ]);

        //  The ShoppingCartService class must be instantiated once
        $this->app->singleton(ShoppingCartService::class, fn($app) => new ShoppingCartService);

        /*
         *  Disable Wrapping API Resources
         *
         *  If you would like to disable the wrapping of the outer-most resource, you may use the
         *  "withoutWrapping" method on the base resource class. Typically, you should call this
         *  method from your AppServiceProvider or another service provider that is loaded on
         *  every request to your application:
         *  Reference: https://laravel.com/docs/5.7/eloquent-resources#concept-overview
         *
         */
        JsonResource::withoutWrapping();
    }
}
