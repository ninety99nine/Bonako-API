<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class OrderHasNoPendingPaymentException extends Exception
{
    protected $message = 'You do not have any pending payments for this order';

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
