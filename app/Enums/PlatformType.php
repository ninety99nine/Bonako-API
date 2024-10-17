<?php

namespace App\Enums;

enum PlatformType:string {
    case WEB = 'web';
    case SMS = 'sms';
    case USSD = 'ussd';
    case MOBILE = 'mobile';
}
