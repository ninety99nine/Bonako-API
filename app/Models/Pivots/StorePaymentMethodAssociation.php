<?php

namespace App\Models\Pivots;

use App\Models\Store;
use App\Models\PaymentMethod;
use App\Models\Base\BasePivot;

class StorePaymentMethodAssociation extends BasePivot
{
    const SUPPORTED_PAYMENT_METHOD_INSTRUCTION_MAX_CHARACTERS = 200;

    protected $casts = [
        'active' => 'boolean'
    ];

    const VISIBLE_COLUMNS = [
        'id', 'active', 'instruction', 'total_enabled', 'total_disabled', 'created_at', 'updated_at'
    ];

    /**
     *  Returns the associated store
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     *  Returns the associated payment method
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

}
