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
    public function eagerLoadRelationships($model) {

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
         *          'total_summarized' => '1k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) use ($store) {

            //  Count the store reviews with the specified filter
            $total = $this->queryStoreReviews($store, $filter)->count();

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
        $reviews = $this->queryStoreReviews($store, $filter);

        //  Eager load the review relationships based on request inputs
        return $this->eagerLoadRelationships($reviews)->get();
    }

    /**
     *  Query the stores by the specified filter
     *
     *  @param Store $store - The store to load the reviews
     *  @param string $filter - The filter to query the reviews
     *  @return \Illuminate\Database\Eloquent\Builder
     */
    public function queryStoreReviews($store, $filter)
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
         *          'total_summarized' => '1k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) use ($user) {

            //  Count the user reviews with the specified filter
            $total = $this->queryUserReviews($user, $filter)->count();

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
        $reviews = $this->queryUserReviews($user, $filter);

        //  Eager load the review relationships based on request inputs
        return $this->eagerLoadRelationships($reviews)->get();
    }

    /**
     *  Query the stores by the specified filter
     *
     *  @param User $user - The user to load the reviews
     *  @param string $filter - The filter to query the reviews
     *  @return \Illuminate\Database\Eloquent\Builder
     */
    public function queryUserReviews($user, $filter)
    {
        //  Normalize the filter
        $filter = $this->separateWordsThenLowercase($filter);

        //  Set the $userReviewAssociation e.g reviewer or team member
        $userReviewAssociation = $this->separateWordsThenLowercase(request()->input('user_review_association'));

        //  If the user must be associated as a reviewer
        if($userReviewAssociation == 'reviewer') {

            //  Query the reviews where the user is associated as a customer
            $reviews = $user->reviews()->latest();

        //  If the user must be associated as a team member
        }else if($userReviewAssociation == 'team member') {

            //  Query the reviews where the user is associated as a team member
            $reviews = Review::whereHas('store', function ($query) use ($user) {
                $query->whereHas('teamMembers', function ($query2) use ($user) {
                    $query2->joinedTeam()->matchingUserId($user->id);
                });
            });

        }

        //  Get the review subjects
        $subjects = collect(Review::SUBJECTS)->map(fn($filter) => $this->separateWordsThenLowercase($filter));

        if(collect($subjects)->contains($filter)) {

            $reviews = $reviews->where('subject', $filter);

        }

        //  Get the latest reviews first
        $reviews = $reviews->latest();

        return $reviews;
    }
}
