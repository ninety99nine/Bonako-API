<?php

namespace App\Models;

use App\Casts\JsonToArray;
use App\Models\Base\BaseModel;
use App\Traits\Base\BaseTrait;
use App\Enums\PaymentMethodType;
use App\Traits\PaymentMethodTrait;
use App\Enums\PaymentMethodCategory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentMethod extends BaseModel
{
    use HasFactory, BaseTrait, PaymentMethodTrait;

    /**
     *  Magic Numbers
     */
    const NAME_MIN_CHARACTERS = 3;
    const NAME_MAX_CHARACTERS = 40;
    const TYPE_MIN_CHARACTERS = 3;
    const TYPE_MAX_CHARACTERS = 40;
    const INSTRUCTION_MIN_CHARACTERS = 3;
    const INSTRUCTION_MAX_CHARACTERS = 200;

    public static function PAYMENT_METHOD_TYPES(): array
    {
        return array_map(fn($method) => $method->value, PaymentMethodType::cases());
    }

    public static function PAYMENT_METHOD_CATEGORIES(): array
    {
        return array_map(fn($method) => $method->value, PaymentMethodCategory::cases());
    }

    protected $casts = [
        'active' => 'boolean',
        'metadata' => JsonToArray::class,
        'countries' => JsonToArray::class,
        'require_proof_of_payment' => 'boolean',
        'automatically_mark_as_paid' => 'boolean',
        'contact_seller_before_payment' => 'boolean',
    ];

    protected $tranformableCasts = [];

    protected $fillable = [
        'active', 'name', 'type', 'category', 'instruction', 'countries', 'metadata',
        'require_proof_of_payment', 'automatically_mark_as_paid',
        'contact_seller_before_payment', 'position', 'store_id'
    ];

    /****************************
     *  SCOPES                  *
     ***************************/

    public function scopeSearch($query, $searchWord)
    {
        return $query->where('name', 'like', "%$searchWord%")
                     ->orWhere('type', 'like', "%$searchWord%");
    }

    public function scopeActive($query)
    {
        return $query->where('active', '1');
    }

    public function scopeLocal($query)
    {
        return $query->where('category', PaymentMethodCategory::LOCAL->value);
    }

    public function scopeManual($query)
    {
        return $query->where('category', PaymentMethodCategory::MANUAL->value);
    }

    public function scopeAutomated($query)
    {
        return $query->where('category', PaymentMethodCategory::AUTOMATED->value);
    }

    /********************
     *  RELATIONSHIPS   *
     *******************/

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /****************************
     *  ACCESSORS               *
     ***************************/

    protected $appends = [
        'image_url', 'is_manual', 'is_automated'
    ];

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => asset('/images/payment-method-logos/'.$this->type.'.jpg')
        );
    }

    protected function isManual(): Attribute
    {
        return Attribute::make(
            get: fn () => strtolower($this->category) == PaymentMethodCategory::MANUAL
        );
    }

    protected function isAutomated(): Attribute
    {
        return Attribute::make(
            get: fn () => strtolower($this->category) == PaymentMethodCategory::AUTOMATED
        );
    }
}
