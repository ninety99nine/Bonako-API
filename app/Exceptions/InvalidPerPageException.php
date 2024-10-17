<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class InvalidPerPageException extends Exception
{
    public function __construct(string|null $message = null)
    {
        $message = $message ?? "The per page value must be a valid number in order to limit the results";
        parent::__construct($message, Response::HTTP_BAD_REQUEST);
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
