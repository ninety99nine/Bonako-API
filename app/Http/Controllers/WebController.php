<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\Controller;

class WebController extends Controller
{
    public function welcome()
    {
        return view('welcome');
    }

    public function privacyPolicy()
    {
        return view('privacy-policy');
    }

    public function termsOfService()
    {
        return view('terms-of-service');
    }

    public function dataDeletionInstructions()
    {
        return view('data-deletion-instructions');
    }
}
