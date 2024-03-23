<?php

namespace App\Enums;

enum PaymentMethodFilter:string {
    case All = 'All';
    case Active = 'Active';
    case Inactive = 'Inactive';
}
