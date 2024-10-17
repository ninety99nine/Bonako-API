<?php

namespace App\Enums;

enum UserFriendGroupRole:string {
    case CREATOR = 'creator';
    case MEMBER = 'member';
    case ADMIN = 'admin';
}
