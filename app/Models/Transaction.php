<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\Currency;
use App\Casts\Percentage;
use App\Casts\JsonToArray;
use App\Models\Base\BaseModel;
use App\Traits\TransactionTrait;
use App\Casts\TransactionPaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends BaseModel
{
    use HasFactory, TransactionTrait;

    const STATUSES = ['Paid', 'Pending Payment', /* 'Refunded', */];
    const CANCELLATION_REASONS = ['Refund', 'Mistake', 'Other'];
    const FILTERS = ['All', ...self::STATUSES];
    const VERIFIERS = ['System', 'User'];

    protected $casts = [
        'amount' => Money::class,
        'is_cancelled' => 'boolean',
        'dpo_payment_url_expires_at' => 'datetime',
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
        'payment_status', 'description', 'proof_of_payment_photo',

        /*  Amount Information  */
        'currency', 'amount', 'percentage', 'payment_method_id',

        /*  DPO Information  */
        'dpo_payment_url', 'dpo_payment_url_expires_at', 'dpo_payment_response',

        /*  Orange Money Information  */
        'orange_money_payment_response',

        /*  Payer Information  */
        'paid_by_user_id',

        /*  Verifier Information  */
        'verified_by', 'verified_by_user_id',

        /*  Requester Information  */
        'requested_by_user_id',

        /*  Cancellation Information  */
        'is_cancelled', 'cancellation_reason', 'cancelled_by_user_id',

        /*  Owenership Details  */
        'owner_id', 'owner_type'

    ];

    /****************************
     *  SCOPES                  *
     ***************************/

    /*
     *  Scope: Return stores that are being searched
     */
    public function scopeSearch($query, $searchWord)
    {
        return $query
            ->where('owner_type', 'like', '%' . $searchWord . '%')
            ->orWhere('description', 'like', '%' . $searchWord . '%')
            ->orWhereHas('paidByUser', function ($paidByUser) use ($searchWord) {
                $paidByUser->search($searchWord);
            });
    }

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
        return $query->where('paid_by_user_id', request()->auth_user->id);
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
    public function paidByUser()
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }

    /**
     *  Returns the associated User that requested this payment transaction.
     *
     *  When a payment is requested, this payment is verified by the system,
     *  therefore when the requestedByUser() is set, then we expect that the
     *  verifiedByUser() must not be set since the verification is done by
     *  the system.
     *
     *  Either the requestedByUser() is set or the verifiedByUser() is set.
     *  They cannot be both set since they indicate the verifier, whether
     *  the transaction is verified by the user or by the system, if the
     *  transaction is PAID.
     */
    public function requestedByUser()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    /**
     *  Returns the User that manually verified this payment transaction.
     *
     *  When a payment is verified manually, then the payment is verified by
     *  the user and not by the system, therefore when the verifiedByUser()
     *  is set, then we expect that the requestedByUser() must not be set
     *  since a system verified request is not issued.
     *
     *  Either the requestedByUser() is set or the verifiedByUser() is set.
     *  They cannot be both set since they indicate the verifier, whether
     *  the transaction is verified by the user or by the system, if the
     *  transaction is PAID.
     */
    public function verifiedByUser()
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

    protected $appends = [
        'number', 'is_paid', 'is_pending_payment',
        'is_verified_by_user', 'is_verified_by_system',
        'is_subject_to_user_verification', 'is_subject_to_system_verification',
        'dpo_payment_link_has_expired'
    ];

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
     *  Check if the transaction is subject to user verification
     *
     *  @return bool
     */
    public function getIsSubjectToUserVerificationAttribute()
    {
        return strtolower($this->getRawOriginal('verified_by')) === 'user';
    }

    /**
     *  Check if the transaction is subject to system verification
     *
     *  @return bool
     */
    public function getIsSubjectToSystemVerificationAttribute()
    {
        return strtolower($this->getRawOriginal('verified_by')) === 'system';
    }

    /**
     *  Check if the transaction payment has been verified by the user
     *
     *  @return bool
     */
    public function getIsVerifiedByUserAttribute()
    {
        return strtolower($this->getRawOriginal('payment_status')) === 'paid' && strtolower($this->getRawOriginal('verified_by')) === 'user';
    }

    /**
     *  Check if the transaction payment has been verified by the system
     *
     *  @return bool
     */
    public function getIsVerifiedBySystemAttribute()
    {
        return strtolower($this->getRawOriginal('payment_status')) === 'paid' && strtolower($this->getRawOriginal('verified_by')) === 'system';
    }

    public function getDpoPaymentLinkHasExpiredAttribute()
    {
        if( $this->dpo_payment_url ) {

            return \Carbon\Carbon::parse($this->dpo_payment_url_expires_at)->isBefore(now());

        }else{

            return null;

        }
    }


}
