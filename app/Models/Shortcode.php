<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Traits\ShortcodeTrait;
use App\Models\Base\BaseModel;
use App\Services\Ussd\UssdService;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shortcode extends BaseModel
{
    use HasFactory, ShortcodeTrait;

    const ACTIONS = ['Pay', 'Visit'];

    protected $dates = ['expires_at'];

    protected $tranformableCasts = [];

    /**
     *  This is the grace period after the shortcode expires.
     *  After these number of days, the shortcode can be
     *  recycled and used by another user
     */
    const GRACE_PERIOD = 30;

    protected $fillable = [

        /*  Basic Information  */
        'code', 'action', 'expires_at',

        /*  Reservation Information  */
        'reserved_for_user_id',

        /*  Owenership Details  */
        'owner_id', 'owner_type'

    ];

    public function scopeAction($query, $action)
    {
        return $query->where('action', ucfirst($action));
    }

    public function scopePayable($query)
    {
        return $query->where('action', 'Pay');
    }

    public function scopeVisitable($query)
    {
        return $query->where('action', 'Visit');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', Carbon::now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>=', Carbon::now());
    }

    public function scopeBelongsToAuth($query)
    {
        return $query->where('reserved_for_user_id', auth()->user()->id);
    }

    /****************************
     *  RELATIONSHIPS           *
     ***************************/

    /**
     *  Get the owning resource e.g Transaction, Store, e.t.c
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /**
     *  Returns the User permitted to dial this shortcode
     */
    public function reservedUser()
    {
        return $this->belongsTo(User::class, 'reserved_for_user_id');
    }

    /****************************
     *  ACCESSORS               *
     ***************************/

    protected $appends = ['dial'];

    public function getDialAttribute()
    {
        //  Set the resource dialing code
        $dialingCode = UssdService::appendToMainShortcode($this->code);

        //  Set the resource name e.g store, transaction, e.t.c
        $resourceName = Str::replace('_', ' ', $this->owner_type);

        if($this->owner_type == 'sms alert') {

            $instruction = 'Dial '.$dialingCode.' to buy SMS alerts';

        }else{

            //  Set the instruction
            $instruction = $this->forPaying()
                ? 'Dial '.$dialingCode.' to pay for this '.$resourceName
                : 'Dial '.$dialingCode.' to visit this '.$resourceName;

        }

        return [
            'code' => $dialingCode,
            'instruction' => $instruction
        ];
    }
}
