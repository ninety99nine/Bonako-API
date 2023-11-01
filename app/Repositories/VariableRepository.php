<?php

namespace App\Repositories;

use App\Models\Variable;
use App\Repositories\BaseRepository;

class VariableRepository extends BaseRepository
{
    protected $requiresConfirmationBeforeDelete = false;

    /**
     *  Eager load relationships on the given model
     *
     *  @param \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder $model
     *  @return VariableRepository
     */
    public function eagerLoadVariableRelationships($model) {

        $relationships = [];
        $countableRelationships = [];

        //  Check if we want to eager load the variables on this product
        if( request()->input('with_product') ) {

            //  Additionally we can eager load the product on this variable
            array_push($relationships, 'product');

        }

        if( !empty($relationships) ) {

            $model = ($model instanceof Variable)
                ? $model->load($relationships)->loadCount($countableRelationships)
                : $model->with($relationships)->withCount($countableRelationships);

        }

        $this->setModel($model);

        return $this;
    }

    /**
     *  Show the variable
     */
    public function showVariable()
    {
        return $this->eagerLoadVariableRelationships($this->model)->get();
    }
}
