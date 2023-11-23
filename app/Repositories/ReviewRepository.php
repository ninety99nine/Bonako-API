<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Review;
use App\Models\Store;
use App\Traits\Base\BaseTrait;
use App\Repositories\BaseRepository;

class ReviewRepository extends BaseRepository
{
    use BaseTrait;

    /**
     *  Eager load relationships on the given model
     *
     *  @param \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder $model
     *  @return OrderRepository
     */
    public function eagerLoadReviewRelationships($model) {

        $relationships = [];
        $countableRelationships = [];

        //  Check if we want to eager load the user on this review
        if( request()->input('with_user') ) {

            //  Additionally we can eager load the user on this review
            array_push($relationships, 'user');

        }

        //  Check if we want to eager load the store on this review
        if( request()->input('with_store') ) {

            //  Additionally we can eager load the store on this review
            array_push($relationships, 'store');

        }

        if( !empty($relationships) ) {

            $model = ($model instanceof Review)
                ? $model->load($relationships)->loadCount($countableRelationships)
                : $model->with($relationships)->withCount($countableRelationships);

        }

        $this->setModel($model);

        return $this;
    }

    /**
     *  Show the user review filters
     *
     *  @param Store $store
     *  @return array
     */
    public function showStoreReviewFilters(Store $store)
    {
        //  Get the store review filters
        $filters = collect(Review::STORE_REVIEW_FILTERS);

        //  Get the review subjects
        $subjects = collect(Review::SUBJECTS)->map(fn($filter) => $this->separateWordsThenLowercase($filter));

        /**
         *  $result = [
         *      [
         *          'name' => 'All',
         *          'total' => 6000,
         *          'total_summarized' => '6k'
         *      ],
         *      [
         *          'name' => 'Product',
         *          'total' => 2000,
         *          'total_summarized' => '2k'
         *      ],
         *      [
         *          'name' => 'Customer Service',
         *          'total' => 1000k,
         *          'total_summarized' => '6k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) use ($store) {

            //  Count the store reviews with the specified filter
            $total = $this->queryStoreReviewsByFilter($store, $filter)->count();

            return [
                'name' => ucwords($filter),
                'total' => $total,
                'total_summarized' => $this->convertNumberToShortenedPrefix($total)
            ];

        })->toArray();
    }

    /**
     *  Show the store reviews
     *
     *  @param User $user
     *  @return ReviewRepository
     */
    public function showStoreReviews(Store $store)
    {
        //  Get the specified filter
        $filter = $this->separateWordsThenLowercase(request()->input('filter'));

        //  Query the store reviews with the specified filter
        $reviews = $this->queryStoreReviewsByFilter($store, $filter);

        //  Eager load the review relationships based on request inputs
        return $this->eagerLoadReviewRelationships($reviews)->get();
    }

    /**
     *  Query the stores by the specified filter
     *
     *  @param Store $store - The store to load the reviews
     *  @param string $filter - The filter to query the reviews
     *  @return \Illuminate\Database\Eloquent\Builder
     */
    public function queryStoreReviewsByFilter($store, $filter)
    {
        //  Get the latest reviews first
        $reviews = $store->reviews()->latest();

        //  Get the specified user id
        $userId = request()->input('user_id');

        //  Get the specified filter
        $filter = $this->separateWordsThenLowercase($filter);

        //  Get the review subjects
        $subjects = collect(Review::SUBJECTS)->map(fn($filter) => $this->separateWordsThenLowercase($filter));

        if(collect($subjects)->contains($filter)) {

            $reviews = $reviews->where('subject', $filter);

        }elseif(!empty($userId) || $filter == 'me') {

            $userId = !empty($userId) ? $userId : auth()->user()->id;

            $reviews = $reviews->where('user_id', $userId);

        }

        return $reviews;
    }

    /**
     *  Show the user review filters
     *
     *  @param User $user
     *  @return array
     */
    public function showUserReviewFilters(User $user)
    {
        //  Get the user review filters
        $filters = collect(Review::USER_REVIEW_FILTERS);

        /**
         *  $result = [
         *      [
         *          'name' => 'All',
         *          'total' => 6000,
         *          'total_summarized' => '6k'
         *      ],
         *      [
         *          'name' => 'Product',
         *          'total' => 2000,
         *          'total_summarized' => '2k'
         *      ],
         *      [
         *          'name' => 'Customer Service',
         *          'total' => 1000k,
         *          'total_summarized' => '6k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) use ($user) {

            //  Count the user reviews with the specified filter
            $total = $this->queryUserReviewsByFilter($user, $filter)->count();

            return [
                'name' => ucwords($filter),
                'total' => $total,
                'total_summarized' => $this->convertNumberToShortenedPrefix($total)
            ];

        })->toArray();
    }

    /**
     *  Show the user reviews
     *
     *  @param User $user
     *  @return ReviewRepository
     */
    public function showUserReviews(User $user)
    {
        //  Get the specified filter
        $filter = $this->separateWordsThenLowercase(request()->input('filter'));

        //  Query the user reviews with the specified filter
        $reviews = $this->queryUserReviewsByFilter($user, $filter);

        //  Eager load the review relationships based on request inputs
        return $this->eagerLoadReviewRelationships($reviews)->get();
    }

    /**
     *  Query the stores by the specified filter
     *
     *  @param User $user - The user to load the reviews
     *  @param string $filter - The filter to query the reviews
     *  @return \Illuminate\Database\Eloquent\Builder
     */
    public function queryUserReviewsByFilter($user, $filter)
    {
        //  Get the latest reviews first
        $reviews = $user->reviews()->latest();

        //  Get the specified filter
        $filter = $this->separateWordsThenLowercase($filter);

        //  Get the review subjects
        $subjects = collect(Review::SUBJECTS)->map(fn($filter) => $this->separateWordsThenLowercase($filter));

        if(collect($subjects)->contains($filter)) {

            $reviews = $reviews->where('subject', $filter);

        }

        return $reviews;
    }
}
