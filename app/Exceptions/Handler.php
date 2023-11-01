<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Response;
use App\Services\Logging\SlackLogError;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        RepositoryQueryFailedException::class,
        AcceptingTermsAndConditionsFailedException::class
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
        $this->reportable(function (Throwable $e) {
            //  Send every error to our Slack error channel
            //  resolve(SlackLogError::class)->logError($e);
        });

        //  Route not found Error
        $this->renderable(function (RouteNotFoundException $e, $request) {
            return response(['message' => 'Route not found Make sure you are using the correct url'], Response::HTTP_NOT_FOUND);
        });

        //  Resource not found Error
        $this->renderable(function (NotFoundHttpException $e, $request) {
            $message = /* $e->getMessage() ?: */ 'This resource does not exist';
            return response(['message' => $message], Response::HTTP_NOT_FOUND);
        });

        //  Method not allowed Error
        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            return response(['message' => 'The '.$request->method().' method not allowed for this endpoint'], Response::HTTP_NOT_FOUND);
        });

        //  Unauthenticated Error
        $this->renderable(function (AuthenticationException $e, $request) {
            return response(['message' => 'Please sign in to continue'], Response::HTTP_UNAUTHORIZED);
        });

        //  Unauthorized Error
        $this->renderable(function (AccessDeniedHttpException $e, $request) {
            //  If this exception does not have a custom message then set a default message
            $message = $e->getMessage() ?: 'You do not have permissions to perform this action';
            return response(['message' => $message], Response::HTTP_FORBIDDEN);
        });

        //  Validation Error
        $this->renderable(function (ValidationException $e, $request) {
            return response([
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        });

        //  Any other Error
        $this->renderable(function (Throwable $e, $request) {

            //  Render any other error
            return response([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ], 500);

        });

    }
}
