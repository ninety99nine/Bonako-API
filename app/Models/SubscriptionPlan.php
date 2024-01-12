<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\Status;
use App\Casts\Currency;
use App\Casts\JsonToArray;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubscriptionPlan extends BaseModel
{
    use HasFactory;

    const TYPES = ['Subscription', 'One-Off'];
    const SERVICES = ['Store Access', 'Store Reporting Access', 'AI Assistant Access', 'SMS Alerts'];

    const STORE_SERVICE_NAME = 'Store Access';
    const SMS_ALERT_SERVICE_NAME = 'SMS Alerts';
    const AI_ASSISTANT_SERVICE_NAME = 'AI Assistant Access';

    protected $casts = [
        'active' => 'boolean',
        'price' => Money::class,
        'metadata' => JsonToArray::class,
    ];

    protected $tranformableCasts = [
        'active' => Status::class,
        'currency' => Currency::class,
    ];

    protected $fillable = [

        /*  Basic Information  */
        'name', 'description', 'service', 'type', 'currency', 'price', 'position', 'active', 'metadata'

    ];

    /****************************
     *  ACCESSORS               *
     ***************************/

    protected $appends = [
        'is_customizable', 'has_customizable_duration', 'has_customizable_sms_credits',
        'applies_duration', 'applies_sms_credits'
    ];

    /**
     *  Check if this subscription plan is customizable.
     *
     *  When the subscription plan is customizable it means that the user
     *  can determine the subscription plan duration or sms credit limit
     */
    public function isCustomizable(): Attribute
    {
        return new Attribute(
            get: fn() => $this->has_customizable_duration || $this->has_customizable_sms_credits
        );
    }

    /**
     *  Check if this subscription plan duration is customizable.
     *
     *  When the subscription plan duration is customizable, it means that the user
     *  can determine the subscription plan duration limit
     */
    public function hasCustomizableDuration(): Attribute
    {
        return new Attribute(
            get: fn() => $this->applies_duration && $this->metadata['duration'] == null
        );
    }

    /**
     *  Check if this subscription plan applies a duration
     */
    public function appliesDuration(): Attribute
    {
        /**
         *  Note that isset() returns false on isset($this->metadata['duration']),
         *  since isset checks if a variable is set and is not null. Since our
         *  value can be null we will use array_key_exists instead.
         */
        return new Attribute(
            get: fn() => array_key_exists('duration', $this->metadata)
        );
    }

    /**
     *  Check if this subscription plan sms credits are customizable.
     *
     *  When the subscription plan sms credits are customizable, it means that the user
     *  can determine the subscription plan sms credit limit
     */
    public function hasCustomizableSmsCredits(): Attribute
    {
        return new Attribute(
            get: fn() => $this->applies_sms_credits && $this->metadata['sms_credits'] == null
        );
    }

    /**
     *  Check if this subscription plan applies sms credits
     */
    public function appliesSmsCredits(): Attribute
    {
        /**
         *  Note that isset() returns false on isset($this->metadata['sms_credits']),
         *  since isset checks if a variable is set and is not null. Since our
         *  value can be null we will use array_key_exists instead.
         */
        return new Attribute(
            get: fn() => array_key_exists('sms_credits', $this->metadata)
        );
    }

}
