<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class DPOCompanyTokenNotProvidedException extends Exception
{
    protected $message = 'The DPO company token is required';

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
