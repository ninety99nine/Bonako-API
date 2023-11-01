<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\Route;

class MarkOrderAsSeenByTeamMember
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
         *  @var User $user
         */
        $user = request()->user();

        //  Check if we are requesting to view a specific order
        if(Route::currentRouteName() == 'order.show') {

            //  Check if the user has the permission to manage the requested order
            if( $user->hasStorePermissionUsingRequest($request, 'manage orders') ) {

                //   Mark this order as viewed by this authenticated user
                $this->orderRepository()->setModel($request->order)->markAsSeen();

            }

        }

        return $next($request);
    }

    /**
     *  Return the OrderRepository instance
     *
     *  @return OrderRepository
     */
    public function orderRepository()
    {
        return resolve(OrderRepository::class);
    }
}
