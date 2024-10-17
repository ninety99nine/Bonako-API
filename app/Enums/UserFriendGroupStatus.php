<?php

namespace App\Enums;

enum UserFriendGroupStatus:string {
    case DECLINED = 'declined';
    case INVITED = 'invited';
    case JOINED = 'joined';
    case LEFT = 'left';
}
