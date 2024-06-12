<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Ussd\UssdService;
use App\Http\Controllers\Base\Controller;

class UssdController extends Controller
{
    public function launchUssd(Request $request)
    {
        return UssdService::launchUssd($request);
    }
}
