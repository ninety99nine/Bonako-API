<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\PayloadNamingConvention;

class FormatResponsePayload
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
         *  @var \Illuminate\Http\Response $response
         */
        $response = $next($request);

        //  Get the original response content
        $responseContent = $response->getContent();

        //  If the original response content is a string or null
        if(!Str::of($responseContent)->isJson()) {

            //  Return the response as is
            return $response;

        }else{

            //  Get the response content as JSON string and convert it into an associative array
            $responseContent = json_decode($response->getContent(), true);

            //  Convert the outgoing response payload to snakecase or camelcase format
            return $response->setContent(
                json_encode(
                    (new PayloadNamingConvention($responseContent))->removeDotNotation()->convertToSuitableFormat()
                )
            );

        }
    }
}
