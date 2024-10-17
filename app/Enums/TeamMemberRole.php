<?php

namespace App\Enums;

enum TeamMemberRole:string {
    case TEAM_MEMBER = 'team member';
    case CREATOR = 'creator';
    case ADMIN = 'admin';
}
