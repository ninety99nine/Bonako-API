<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class TransactionReservedForAnotherUser extends Exception
{
    protected $message = 'This transaction is restricted for another user';

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
