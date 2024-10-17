<?php

namespace App\Enums;

enum PricingPlanBillingType:string {
    case ONE_TIME = 'one time';
    case RECURRING = 'recurring';
}
