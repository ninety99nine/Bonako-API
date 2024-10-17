<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class RepositoryDatabaseQueryFailedException extends Exception
{
    public function __construct(string $modelName)
    {
        $message = "Could not get the {$modelName} records due to a database query failure.";
        parent::__construct($message, Response::HTTP_NOT_FOUND);
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
