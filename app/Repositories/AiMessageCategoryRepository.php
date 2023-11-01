<?php

namespace App\Repositories;

use App\Models\AiMessageCategory;
use Illuminate\Http\Request;
use App\Traits\Base\BaseTrait;
use App\Repositories\BaseRepository;

class AiMessageCategoryRepository extends BaseRepository
{
    use BaseTrait;

    /**
     *  Eager load relationships on the given model
     *
     *  @param \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder $model
     *  @return AiMessageCategoryRepository
     */
    public function eagerLoadAiMessageCategoryRelationships($model) {

        $relationships = [];
        $countableRelationships = [];

        if( !empty($relationships) ) {

            $model = ($model instanceof AiMessageCategory)
                ? $model->load($relationships)->loadCount($countableRelationships)
                : $model->with($relationships)->withCount($countableRelationships);

        }

        $this->setModel($model);

        return $this;
    }

    /**
     *  Show the AI message category while eager loading any required relationships
     *
     *  @return AiMessageCategoryRepository
     */
    public function showAiMessageCategory()
    {
        /**
         *  @var AiMessageCategory $aiMessageCategory
         */
        $aiMessageCategory = $this->model;

        //  Eager load the AI message category relationships based on request inputs
        return $this->eagerLoadAiMessageCategoryRelationships($aiMessageCategory);
    }

    /**
     *  Show the AI message categories while eager loading any required relationships
     *
     *  @return AiMessageCategoryRepository
     */
    public function showAiMessageCategories()
    {
        $aiMessageCategories = $this->model;

        //  Eager load the AI message category relationships based on request inputs
        return $this->eagerLoadAiMessageCategoryRelationships($aiMessageCategories)->get();
    }
}
