<?php

namespace App\Enums;

enum ReturnType:string {
    case ARRAY = 'array';
    case MODEL = 'model';
    case SELF = 'self';
    case ID = 'id';
}
