<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\PayloadLimiter;

class ResponsePayloadLimiter
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

        //  Check if the payload limiter is provided
        $hasPayloadLimiter = request()->filled('_select');

        //  If the original response content is a string or null or the payload limiter isn't provided
        if(!Str::of($responseContent)->isJson() || !$hasPayloadLimiter) {

            //  Return the response as is
            return $response;

        }else{

            //  Get the payload limiter
            $payloadLimiter = request()->input('_select');

            //  Get the response content as JSON string and convert it into an associative array
            $responseContent = json_decode($response->getContent(), true);

            //  Return the response content with or without limited payload
            return $response->setContent(
                json_encode((new PayloadLimiter($responseContent, $payloadLimiter))->getLimitedPayload())
            );

        }
    }
}
