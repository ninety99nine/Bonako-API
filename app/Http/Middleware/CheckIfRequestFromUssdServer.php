<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Ussd\UssdService;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CheckIfRequestFromUssdServer
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
        //  Check if the ussd token is provided on the request
        if($request->filled('ussd_token')) {

            //  Check if the ussd token is a string value
            if(is_string($request->input('ussd_token'))) {

                //  Check if the request is coming from the USSD server
                if( UssdService::verifyIfRequestFromUssdServer() ){

                    //  Allow access
                    return $next($request);

                }else{

                    //  Deny access
                    throw new AccessDeniedHttpException;

                }

            }else{

                //  Throw an Exception - Incorrect token data type
                throw ValidationException::withMessages(['ussd_token' => 'The ussd token must be a string']);

            }

        }else{

            //  Throw an Exception - The ussd token is required
            throw ValidationException::withMessages(['ussd_token' => 'The ussd token is required']);

        }
    }
}
