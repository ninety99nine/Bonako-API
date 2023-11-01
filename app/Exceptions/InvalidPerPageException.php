<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class InvalidPerPageException extends Exception
{
    protected $message = 'The per page value must be a valid number in order to limit the results';

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return response(['message' => $this->message], Response::HTTP_BAD_REQUEST);
    }
}
