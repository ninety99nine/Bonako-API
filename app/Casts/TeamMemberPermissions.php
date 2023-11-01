<?php

namespace App\Casts;

use App\Traits\Base\BaseTrait;
use App\Repositories\StoreRepository;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class TeamMemberPermissions implements CastsAttributes
{
    use BaseTrait;

    /**
     *  Return the StoreRepository instance
     *
     *  @return StoreRepository
     */
    public function storeRepository()
    {
        return resolve(StoreRepository::class);
    }

    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array
     */
    public function get($model, $key, $value, $attributes)
    {
        //  Convert from string to array
        $permissions = (new JsonToArray)->get($model, $key, $value, $attributes);

        //  Transform permissions
        return $this->storeRepository()->extractPermissions($permissions);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  array  $value
     * @param  array  $attributes
     * @return string
     */
    public function set($model, $key, $value, $attributes)
    {
        return $value;
    }
}
