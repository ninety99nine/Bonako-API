<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class OrderHasNoAmountOutstandingException extends Exception
{
    protected $message = 'This order has no amount outstanding that can be paid';

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
