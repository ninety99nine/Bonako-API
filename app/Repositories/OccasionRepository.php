<?php

namespace App\Repositories;

use App\Models\Occasion;
use Illuminate\Http\Request;
use App\Traits\Base\BaseTrait;
use App\Repositories\BaseRepository;

class OccasionRepository extends BaseRepository
{
    use BaseTrait;

    /**
     *  Eager load relationships on the given model
     *
     *  @param \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder $model
     *  @return OccasionRepository
     */
    public function eagerLoadOccasionRelationships($model) {

        $relationships = [];
        $countableRelationships = [];

        if( !empty($relationships) ) {

            $model = ($model instanceof Occasion)
                ? $model->load($relationships)->loadCount($countableRelationships)
                : $model->with($relationships)->withCount($countableRelationships);

        }

        $this->setModel($model);

        return $this;
    }

    /**
     *  Show the occasion while eager loading any required relationships
     *
     *  @return OccasionRepository
     */
    public function showOccasion()
    {
        /**
         *  @var Occasion $occasion
         */
        $occasion = $this->model;

        //  Eager load the occasion relationships based on request inputs
        return $this->eagerLoadOccasionRelationships($occasion);
    }

    /**
     *  Show the occasions while eager loading any required relationships
     *
     *  @return OccasionRepository
     */
    public function showOccasions()
    {
        $occasions = $this->model;

        //  Eager load the occasion relationships based on request inputs
        return $this->eagerLoadOccasionRelationships($occasions)->get();
    }
}
