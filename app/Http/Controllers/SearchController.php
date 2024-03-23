<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Store;
use Illuminate\Http\Response;
use App\Traits\Base\BaseTrait;
use App\Repositories\UserRepository;
use App\Repositories\StoreRepository;
use App\Http\Controllers\Base\Controller;
use App\Repositories\FriendGroupRepository;

class SearchController extends Controller
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
     *  Return the UserRepository instance
     *
     *  @return UserRepository
     */
    public function userRepository()
    {
        return resolve(UserRepository::class);
    }

    /**
     *  Return the FriendGroupRepository instance
     *
     *  @return FriendGroupRepository
     */
    public function friendGroupRepository()
    {
        return resolve(FriendGroupRepository::class);
    }

    public function showSearchFilters()
    {
        $filters = collect(['Stores', 'Friends', 'Groups']);

        /**
         *  @var User $user
         */
        $user = request()->auth_user;

        /**
         *  $result = [
         *      [
         *          'name' => 'Stores',
         *          'total' => 2000,
         *          'total_summarized' => '2k'
         *      ],
         *      [
         *          'name' => 'Friends',
         *          'total' => 1000,
         *          'total_summarized' => '1k'
         *      ],
         *      [
         *          'name' => 'Groups',
         *          'total' => 1000,
         *          'total_summarized' => '1k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) use ($user) {

            if($filter == 'Stores') {

                $total = Store::count();

            }elseif($filter == 'Friends') {

                $total = $user->friends()->count();

            }elseif($filter == 'Groups') {

                $total = $user->friendGroups()->count();

            }

            return [
                'name' => ucwords($filter),
                'total' => $total,
                'total_summarized' => $this->convertNumberToShortenedPrefix($total)
            ];

        })->toArray();
    }

    public function searchStores()
    {
        $storeRepository = $this->storeRepository();

        // Eager load the user and store association
        request()->merge(['with_user_store_association' => '1']);

        /**
         *  @var Store $model
         */
        $model = $storeRepository->eagerLoadRelationships($storeRepository->getModel())->getModel();

        // Order by the total orders (First priority to order)
        if(request()->input('with_count_orders')) $model = $model->orderBy('orders_count', 'desc');

        // Order by the total reviews (Second priority to order)
        if(request()->input('with_count_reviews')) $model = $model->orderBy('reviews_count', 'desc');

        // Order by rating (Third priority to order)
        if(request()->input('with_rating')) $model = $model->orderBy('rating', 'desc');

        // Order by the total followers (Fourth priority to order)
        if(request()->input('followers_count')) $model = $model->orderBy('followers_count', 'desc');

        return $this->prepareOutput($storeRepository->setModel($model)->get());
    }

    public function searchFriends()
    {
        return $this->prepareOutput($this->userRepository()->setModel(request()->auth_user)->showFriends());
    }

    public function searchFriendGroups()
    {
        return $this->prepareOutput($this->friendGroupRepository()->get());
    }
}
