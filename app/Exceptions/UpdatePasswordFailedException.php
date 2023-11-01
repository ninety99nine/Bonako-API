<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class UpdatePasswordFailedException extends Exception
{
    protected $message = 'Failed to update the account password.';

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
