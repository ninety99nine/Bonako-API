<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RequireApiHeaders
{
    public const ACCEPT_JSON_ERROR_MESSAGE = 'Include the [Accept: application/json] as part of your request header before consuming this API';
    public const CONTENT_TYPE_JSON_ERROR_MESSAGE = 'Include the [Content-Type: application/json] as part of your request header before consuming this API';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->acceptsJson($request)) {
            return response()->json(['error' => self::ACCEPT_JSON_ERROR_MESSAGE], Response::HTTP_BAD_REQUEST);
        }

        if (!$this->isJson($request)) {
            return response()->json(['error' => self::CONTENT_TYPE_JSON_ERROR_MESSAGE], Response::HTTP_BAD_REQUEST);
        }

        return $next($request);
    }

    /**
     * Check if the request accepts JSON.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    private function acceptsJson(Request $request): bool
    {
        return $request->acceptsJson();
    }

    /**
     * Check if the request is JSON.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    private function isJson(Request $request): bool
    {
        return $request->isJson();
    }
}
