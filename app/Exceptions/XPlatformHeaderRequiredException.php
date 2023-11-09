<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class XPlatformHeaderRequiredException extends Exception
{
    protected $message = 'The X-Platform header is required to know the origin of this call e.g Include header "X-Platform = Mobile" if these calls originate from the mobile app';

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
