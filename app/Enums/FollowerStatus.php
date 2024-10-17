<?php

namespace App\Enums;

enum FollowerStatus:string {
    case INVITED = 'invited';
    case DECLINED = 'declined';
    case FOLLOWING = 'following';
    case UNFOLLOWED = 'unfollowed';
}
