<?php

namespace App\Exceptions;

use Exception;
use App\Models\Store;
use Illuminate\Http\Response;

class StoreHasTooManyCouponsException extends Exception
{
    protected $message = 'You cannot create more than '.Store::MAXIMUM_COUPONS.' coupons';

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
