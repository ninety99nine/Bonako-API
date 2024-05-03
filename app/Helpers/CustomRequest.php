<?php

namespace App\Helpers;

use Illuminate\Http\Request;

class CustomRequest extends Request
{
    protected $convertedFiles;

    /**
     *  In order to support the ability of converting the request file keys
     *  into snakecase, we need to be able to override the request property
     *  called convertedFiles which is part of the Request.
     *
     */
    public function setConvertedFiles($files)
    {
        $this->convertedFiles = $files;
    }
}

