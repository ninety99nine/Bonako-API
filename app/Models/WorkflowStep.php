<?php

namespace App\Models;

use App\Traits\Base\BaseTrait;
use App\Models\Base\BaseModel;
use App\Services\PhoneNumber\PhoneNumberService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkflowStep extends BaseModel
{
    use HasFactory, BaseTrait;

    protected $fillable = [
        'settings','position','workflow_id',
    ];

    /********************
     *  RELATIONSHIPS   *
     *******************/

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    /****************************
     *  ACCESSORS               *
     ***************************/

    protected function settings(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $settings = $this->jsonToArray($value);

                if(!empty($settings) && !empty($settings['mobile_numbers'])) {

                    $settings['mobile_numbers'] = collect($settings['mobile_numbers'])->map(function($mobileNumber) {
                        return PhoneNumberService::formatPhoneNumber($mobileNumber);
                    })->all();

                }

                return $settings;
            },
            set: fn ($value) => is_array($value) ? json_encode($value) : $value
        );
    }
}
