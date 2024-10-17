<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class FailedRepositoryResolutionException extends Exception
{
    public function __construct($repositoryClass)
    {
        $message = "Failed to resolve repository [{$repositoryClass}]";
        parent::__construct($message, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->json(['message' => $this->getMessage()], $this->getCode());
    }
}
