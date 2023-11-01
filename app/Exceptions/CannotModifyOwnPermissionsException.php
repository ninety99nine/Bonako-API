<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class CannotModifyOwnPermissionsException extends Exception
{
    protected $message = 'You are not allowed to modify your own permissions';

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
