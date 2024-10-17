<?php

namespace App\Enums;

enum TeamMemberStatus:string {
    case LEFT = 'left';
    case JOINED = 'joined';
    case INVITED = 'invited';
    case DECLINED = 'declined';
}
