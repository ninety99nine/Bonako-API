<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class OrderProhibitsMultiplePendingPaymentByUserException extends Exception
{
    protected $message = 'This order cannot request payment since the payer already has pending payments';

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
