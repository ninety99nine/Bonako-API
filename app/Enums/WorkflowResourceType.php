<?php

namespace App\Enums;

enum WorkflowResourceType:string {
    case ORDER = 'order';
    case INVENTORY = 'inventory';
}
