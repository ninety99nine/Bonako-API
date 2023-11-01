<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use App\Http\Controllers\Base\Controller;
use App\Services\MobileNumber\MobileNumberService;
use App\Http\Requests\Models\MobileNumber\ShowMobileNumberUserNameRequest;

class MobileNumberController extends Controller
{
    public function showUserAccount(ShowMobileNumberUserNameRequest $request)
    {
        return response(MobileNumberService::showUserAccount($request->input('mobile_number')), Response::HTTP_OK);
    }
}
