<?php

namespace App\Enums;

enum AccessToken:int {
    case RETURN_ACCESS_TOKEN = 1;
    case DO_NOT_RETURN_ACCESS_TOKEN = 0;
}
