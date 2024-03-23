<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Exceptions\AcceptTermsAndConditionsException;

class AcceptedTermsAndConditions
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
        $user = request()->auth_user;

        //  If the authenticated user has accepted the terms and conditions
        if( $user->accepted_terms_and_conditions ) {

            //  Continue
            return $next($request);

        }else{

            throw new AcceptTermsAndConditionsException();

        }
    }
}
