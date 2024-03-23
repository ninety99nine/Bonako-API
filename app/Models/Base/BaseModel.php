<?php

namespace App\Models\Base;

use App\Traits\Base\BaseTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

/**
 *  Allows the model to define fields that are transformable
 *  for consumption by third-party sources. This allows us
 *  to convienently decide which properties we would like
 *  to share and which we avoid sharing.
 */
abstract class BaseModel extends Model
{
    use BaseTrait;

    /*
     *  Scope: Return results that are being searched.
     */
    public function scopeSearch($query, $searchWord)
    {
        return $query->where('id', $searchWord);
    }

    /**
     * Cast an attribute to a native PHP type.
     *
     *  (1) Fix Eloquent casts array returns null instead of empty array
     *      Reference: https://laracasts.com/discuss/channels/eloquent/eloquent-casts-array-returns-null-instead-of-empty-array
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function castAttribute($key, $value)
    {
        if ($this->getCastType($key) == 'array' && is_null($value)) {
            return [];
        }

        return parent::castAttribute($key, $value);
    }

    /**
     *  Return the transformable appends
     */
    public function getTransformableAppends()
    {
        return collect($this->appends)->except($this->unTransformableAppends ?? [])->toArray();
    }

    /**
     *  Return the transformable casts
     */
    public function getTranformableCasts()
    {
        return $this->tranformableCasts ?? [];
    }
}
