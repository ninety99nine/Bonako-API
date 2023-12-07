<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmsAlertActivity extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'enabled' => 'boolean',
        'requires_stores' => 'boolean',
    ];

    protected $tranformableCasts = [];

    protected $fillable = ['name', 'description', 'enabled', 'requires_stores'];

    /**
     *  Returns the sms alert activity associations
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::hasMany
     */
    public function smsAlertActivityAssociations()
    {
        return $this->hasMany(SmsAlertActivityAssociation::class);
    }
}
