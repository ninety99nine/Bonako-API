<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [

    ];

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register()
    {
        // Route not found Error
        $this->renderable(function (RouteNotFoundException $e, $request) {
            return response(['message' => !empty($message = $e->getMessage()) ? $message : 'Route not found. Make sure you are using the correct URL.'], Response::HTTP_NOT_FOUND);
        });

        // Resource not found Error
        $this->renderable(function (NotFoundHttpException $e, $request) {
            $message = 'This resource does not exist.';
            return response(['message' => $message], Response::HTTP_NOT_FOUND);
        });

        // Method not allowed Error
        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            return response(['message' => 'The ' . $request->method() . ' method is not allowed for this endpoint.'], Response::HTTP_METHOD_NOT_ALLOWED);
        });

        // Unauthenticated Error
        $this->renderable(function (AuthenticationException $e, $request) {
            return response(['message' => 'Please sign in to continue.'], Response::HTTP_UNAUTHORIZED);
        });

        // Unauthorized Error
        $this->renderable(function (AccessDeniedHttpException $e, $request) {
            // If this exception does not have a custom message, then set a default message
            $message = $e->getMessage() ?: 'You do not have permissions to perform this action.';
            return response(['message' => $message], Response::HTTP_FORBIDDEN);
        });

        // Validation Error
        $this->renderable(function (ValidationException $e, $request) {
            return response([
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        // Any other Error
        $this->renderable(function (Throwable $e, $request) {
            $response = ['message' => 'Something went wrong'];

            if (config('app.debug')) {
                $response['error'] = $e->getMessage();
                $response['file'] = $e->getFile();
                $response['line'] = $e->getLine();
                $response['trace'] = $e->getTrace();
            }

            return response($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        });
    }
}
