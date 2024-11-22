<?php

namespace App\Enums;

enum InsightPeriod:string {
    case TODAY = 'today';
    case YESTERDAY = 'yesterday';
    case THIS_WEEK = 'this week';
    case THIS_MONTH = 'this month';
    case THIS_YEAR = 'this year';
}
