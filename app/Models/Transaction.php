<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\Currency;
use App\Casts\JsonToArray;
use App\Casts\Percentage;
use App\Casts\TransactionPaymentStatus;
use App\Casts\Status;
use App\Models\Base\BaseModel;
use App\Traits\TransactionTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends BaseModel
{
    use HasFactory, TransactionTrait;

    const STATUSES = ['Paid', 'Pending Payment', /* 'Refunded', */];
    const CANCELLATION_REASONS = ['Refund', 'Mistake', 'Other'];
    const FILTERS = ['All', ...self::STATUSES];

    protected $casts = [
        'amount' => Money::class,
        'is_cancelled' => 'boolean',
        'dpo_payment_response' => JsonToArray::class,
        'orange_money_payment_response' => JsonToArray::class,
    ];

    protected $tranformableCasts = [
        'currency' => Currency::class,
        'percentage' => Percentage::class,
        'payment_status' => TransactionPaymentStatus::class,
    ];

    protected $fillable = [

        /*  Basic Information  */
        'payment_status', 'description',

        /*  Amount Information  */
        'currency', 'amount', 'percentage', 'payment_method_id',

        /*  DPO Information  */
        'dpo_payment_url', 'dpo_payment_url_expires_at', 'dpo_payment_response',

        /*  Orange Money Information  */
        'orange_money_payment_response',

        /*  Payer Information  */
        'payer_user_id',

        /*  Verifier Information  */
        'verified_by_user_id',

        /*  Requester Information  */
        'requested_by_user_id',

        /*  Cancellation Information  */
        'is_cancelled', 'cancellation_reason', 'cancelled_by_user_id',

        /*  Owenership Details  */
        'owner_id', 'owner_type'

    ];

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'Paid');
    }

    public function scopePendingPayment($query)
    {
        return $query->where('payment_status', 'Pending Payment');
    }

    public function scopeBelongsToAuth($query)
    {
        return $query->where('payer_user_id', auth()->user()->id);
    }

    public function scopeCancelled($query)
    {
        return $query->where('is_cancelled', '1');
    }

    public function scopeNotCancelled($query)
    {
        return $query->where('is_cancelled', '0');
    }

    /****************************
     *  RELATIONSHIPS           *
     ***************************/

    /**
     * Get the owning resource e.g Subscription, Order
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /**
     *  Returns the User responsible to pay for this payment transaction.
     */
    public function payingUser()
    {
        return $this->belongsTo(User::class, 'payer_user_id');
    }

    /**
     *  Returns the associated User that requested this payment transaction.
     *
     *  When a payment is requested, this payment is verified by the system,
     *  therefore when the requestingUser() is set, then we expect that the
     *  verifyingUser() must not be set since the verification is done by
     *  the system.
     *
     *  Either the requestingUser() is set or the verifyingUser() is set.
     *  They cannot be both set since they indicate the verifier, whether
     *  the transaction is verified by the user or by the system, if the
     *  transaction is PAID.
     */
    public function requestingUser()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    /**
     *  Returns the User that manually verified this payment transaction.
     *
     *  When a payment is verified manually, then the payment is verified by
     *  the user and not by the system, therefore when the verifyingUser()
     *  is set, then we expect that the requestingUser() must not be set
     *  since a system verified request is not issued.
     *
     *  Either the requestingUser() is set or the verifyingUser() is set.
     *  They cannot be both set since they indicate the verifier, whether
     *  the transaction is verified by the user or by the system, if the
     *  transaction is PAID.
     */
    public function verifyingUser()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    /**
     *  Returns the payment method for this payment transaction.
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     *  Returns the latest payment shortcodes owned by this transaction
     */
    public function activePaymentShortcode()
    {
        return $this->morphOne(Shortcode::class, 'owner')->action('Pay')->notExpired()->latest();
    }

    /****************************
     *  ACCESSORS               *
     ***************************/

    protected $appends = ['number', 'is_paid', 'is_pending_payment', 'is_verified_by_user', 'is_verified_by_system'];

    public function getNumberAttribute()
    {
        return str_pad($this->id, 5, 0, STR_PAD_LEFT);
    }

    /**
     *  Check if the transaction has been paid
     *
     *  @return bool
     */
    public function getIsPaidAttribute()
    {
        return $this->isPaid();
    }

    /**
     *  Check if the transaction is pending payment
     *
     *  @return bool
     */
    public function getIsPendingPaymentAttribute()
    {
        return $this->isPendingPayment();
    }

    /**
     *  Check if the transaction payment was verified by the user
     *
     *  @return bool
     */
    public function getIsVerifiedByUserAttribute()
    {
        //  If the verified by user id is provided then this transaction was verified by a user
        return $this->payment_status === 'Paid' && $this->verified_by_user_id !== null;
    }

    /**
     *  Check if the transaction payment was verified by the system
     *
     *  @return bool
     */
    public function getIsVerifiedBySystemAttribute()
    {
        //  If the verified by user id is not provided then this transaction was verified by the system
        return $this->payment_status === 'Paid' && $this->verified_by_user_id === null;
    }
}
