<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmsAlertActivityAssociation extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'enabled' => 'boolean',
    ];

    //  protected $with = ['smsAlertActivity', 'stores'];

    protected $fillable = ['enabled', 'total_alerts_sent', 'sms_alert_id', 'sms_alert_activity_id'];

    /**
     *  Returns the sms alert
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function smsAlert()
    {
        return $this->belongsTo(SmsAlert::class);
    }

    /**
     *  Returns the sms alert activity
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function smsAlertActivity()
    {
        return $this->belongsTo(SmsAlertActivity::class);
    }

    /**
     *  Returns the associated stores
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function stores()
    {
        return $this->belongsToMany(Store::class, 'sms_alert_activity_store_associations', 'sms_alert_activity_association_id', 'store_id');
    }
}
