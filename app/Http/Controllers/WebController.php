<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\Controller;

class WebController extends Controller
{
    public function welcome()
    {
        return view('welcome');
    }
}
