<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class InvalidPaginateException extends Exception
{
    protected $message = 'The paginate value must be true or false to decide whether to paginate the results';

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
