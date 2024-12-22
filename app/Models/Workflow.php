<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use App\Traits\Base\BaseTrait;
use App\Enums\WorkflowTriggerType;
use App\Enums\WorkflowResourceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Workflow extends BaseModel
{
    use HasFactory, BaseTrait;

    /**
     *  Magic Numbers
     */
    const NAME_MIN_CHARACTERS = 3;
    const NAME_MAX_CHARACTERS = 60;

    public static function WORKFLOW_TRIGGER_TYPES(): array
    {
        return array_map(fn($method) => $method->value, WorkflowTriggerType::cases());
    }

    public static function WORKFLOW_RESOURCE_TYPES(): array
    {
        return array_map(fn($method) => $method->value, WorkflowResourceType::cases());
    }

    protected $casts = [
        'active' => 'boolean'
    ];

    protected $fillable = [
        'active','name','resource','trigger','position','store_id',
    ];

    /****************************
     *  SCOPES                  *
     ***************************/

    public function scopeSearch($query, $searchWord)
    {
        return $query->where('name', 'like', "%$searchWord%");
    }

    public function scopeActive($query)
    {
        return $query->where('active', '1');
    }

    /********************
     *  RELATIONSHIPS   *
     *******************/

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function workflowSteps()
    {
        return $this->hasMany(WorkflowStep::class);
    }
}
