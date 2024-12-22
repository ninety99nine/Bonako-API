<?php

namespace App\Enums;

enum DeliveryMethodScheduleType:string {
    case DATE = 'date';
    case DATE_AND_TIME = 'date and time';
}
