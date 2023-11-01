<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class InvalidInvitationException extends Exception
{
    protected $message = 'You have not been invited to this store';

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
