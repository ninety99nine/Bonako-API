<?php

namespace App\Enums;

enum ReturnType:string {
    case ARRAY = 'array';
    case MODEL = 'model';
    case NULL = 'null';
    case SELF = 'self';
    case ID = 'id';
}
