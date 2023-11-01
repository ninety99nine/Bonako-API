<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class OrderProhibitsTransactionsWhenCancelledException extends Exception
{
    protected $message = 'This order cannot initiate transactions because it is cancelled';

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
