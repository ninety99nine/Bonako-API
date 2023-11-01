<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class CartAlreadyConvertedException extends Exception
{
    protected $message = 'The shopping cart has already been converted to an order';

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return response(['message' => $this->message], Response::HTTP_FORBIDDEN);
    }
}
