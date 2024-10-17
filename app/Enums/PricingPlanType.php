<?php

namespace App\Enums;

enum PricingPlanType:string {
    case SMS_CREDITS = 'sms credits';
    case EMAIL_CREDITS = 'email credits';
    case WHATSAPP_CREDITS = 'whatsapp credits';
    case STORE_SUBSCRIPTION = 'store subcription';
    case AI_ASSISTANT_SUBSCRIPTION = 'ai assistant subcription';
    case AI_ASSISTANT_TOP_UP_CREDITS = 'ai assistant top up credits';
}
