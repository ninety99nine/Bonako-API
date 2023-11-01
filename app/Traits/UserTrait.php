<?php

namespace App\Traits;

use Exception;
use App\Models\Order;
use App\Models\Store;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Traits\Base\BaseTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Pivots\UserStoreAssociation;
use Illuminate\Support\Facades\DB;

trait UserTrait
{
    use BaseTrait;

    public function isSuperAdmin()
    {
        return $this->is_super_admin;
    }

    /**
     *  Update the last time the user was seen using services
     *  @return void
     */
    public function updateLastSeen() {
        $this->update(['last_seen_at' => now()]);
    }

    /**
     *  Update the last time the user was seen using services
     *  while at a particular store
     *  @return void
     */
    public function updateLastSeenAtStore(Store $store) {
        UserStoreAssociation::updateOrCreate(
            ['store_id' => $store->id, 'user_id' => $this->id],
            [
                'store_id' => $store->id,
                'user_id' => $this->id,
                'last_seen_at' => now(),
            ]
        );
    }

    /**
     *  Check if the current authenticated user is assigned to the given store
     *  with the given roles
     *
     *  @param Store|int $store
     *  @param array $roles
     *
     *  @return bool
     */
    public function isAssignedToStoreAsTeamMember($store, $roles = [])
    {
        $id = $store instanceof Model ? $store->id : $store;

        /**
         *  Use the specified roles otherwise default to collecting every role
         */
        $roles = count($roles) ? $roles : UserStoreAssociation::TEAM_MEMBER_ROLES;

        return Store::where('id', $id)->whereHas('teamMembers', function (Builder $stores) use ($roles) {

            return $stores->where('user_store_association.user_id', $this->id)
                    ->where('team_member_status', 'Joined')
                    ->whereIn('team_member_role', $roles);

        })->exists();
    }

    /**
     *  Check if the current authenticated user has the given permissions on the store
     *  by passing the request as a parameter to be checked
     *
     *  @param Request $request
     *  @param string $permission
     *
     *  @return bool
     */
    public function hasStorePermissionUsingRequest(Request $request, $permission)
    {
        //  Initialise the model
        $model = null;

        //  If we have the store via the request
        if( $request->store ) {

            //  Get the store
            $model = $request->store;

        //  If we have the product via the request
        }elseif( $request->product ) {

            //  Get the product
            $model = $request->product;

        //  If we have the order via the request
        }elseif( $request->order ) {

            //  Get the order
            $model = $request->order;

        }

        //  If we have a requets model e.g store, order, product, e.t.c
        if($model) {

            return $this->hasStorePermission($model, $permission);

        }else{

            throw new Exception('This route does not contain the store id required to check permissions', Response::HTTP_BAD_REQUEST);

        }
    }

    /**
     *  Check if the current authenticated user has the given permissions on the store
     *
     *  @param ?Model $model
     *  @param string $permission
     *
     *  @return bool
     */
    public function hasStorePermission(?Model $model, string $permission)
    {
        //  Initialise the store
        $store_id = null;

        //  If we have the store as the model
        if( $model instanceof Store ) {

            //  Get the store id
            $store_id = $model->id;

        //  If we have the order as the model
        }elseif( $model instanceof Order ) {

            //  Get the order store id
            $store_id = $model->store_id;

        //  If we have the product as the model
        }elseif( $model instanceof Product ) {

            //  Get the product store id
            $store_id = $model->store_id;

        }

        //  If we have the store id
        if($store_id) {

            //  Check if we have the permissions stored in cache memory
            $hasPermissions = $this->getHasStorePermissionFromCache($store_id, $permission);

            //  If the permissions are not stored in cache memory
            if( $hasPermissions == null ) {

                //  Get the matching store
                if( $store = $this->storesAsTeamMember()->joinedTeam()->where('store_id', $store_id)->first() ) {

                    //  Check if the user has the given permissions on the store
                    $hasPermissions = collect($store->user_store_association->team_member_permissions)->contains(function($teamMemberPermission) use ($permission) {

                        //  Check if we have all permissions or atleast the permission required
                        return ($teamMemberPermission['grant'] == '*') || (strtolower($teamMemberPermission['grant']) == strtolower($permission));

                    });

                }

            }

            /**
             *  Add the permission status as a cache value so that we limit
             *  the numbers of times that we have to make this request. If
             *  the cache value already exists, then overide to extend the
             *  time to expiry.
             */
            $this->addHasStorePermissionIntoCache($store_id, $permission, $hasPermissions);

            return $hasPermissions;

        }else{

            throw new Exception('This route does not contain the store id required to check permissions.', Response::HTTP_BAD_REQUEST);

        }
    }

    /**
     *  Check if the current authenticated user has the given permissions on the store
     *  by checking the cache
     *
     *  @param int $model
     *  @param string $permission
     *
     *  @return bool
     */
    public function getHasStorePermissionFromCache($store_id, $permission)
    {
        $key = $this->getHasStorePermissionCacheName($store_id, $permission);
        return Cache::get($key);
    }

    /**
     *  Add a cache value which shows that the current authenticated user
     *  has the given permissions on the store. This cache value is valid
     *  for one day.
     *
     *  @param int $model
     *  @param string $permission
     *  @param boolean $status
     *
     *  @return bool
     */
    public function addHasStorePermissionIntoCache($store_id, $permission, $status)
    {
        $key = $this->getHasStorePermissionCacheName($store_id, $permission);
        return Cache::put($key, $status, now()->addDay());
    }

    /**
     *  Remove the cache value which shows that the current authenticated user
     *  has the given permissions on the store
     *
     *  @param int $model
     *  @param string $permission
     *
     *  @return bool
     */
    public function removeHasStorePermissionFromCache($store_id, $permission)
    {
        $key = $this->getHasStorePermissionCacheName($store_id, $permission);
        return Cache::forget($key);
    }

    /**
     *  Check if the current authenticated user has the given permissions on the store
     *  by checking the cache
     *
     *  @param int $model
     *  @param string $permission
     *
     *  @return bool
     */
    public function getHasStorePermissionCacheName($store_id, $permission)
    {
        /**
         *  If the $store_id is equal to "5" and the authenticated user's id
         *  is equal to "1", then the returned result must be
         *  "PERMISSION_TO_MANAGE_ORDERS_5_1"
         */
        $permission = strtoupper(str_replace(' ', '_', $permission));

        return 'PERMISSION_TO_'.$permission.'_'.$store_id.'_'.auth()->user()->id;
    }

    /**
     *  Craft the new order sms messsage to send to the customer
     *
     *  @return Order
     */
    public function craftAccountCreatedSmsMessageForUser() {
        return 'Hi '.$this->first_name.', your '.config('app.name').' account was created successfully! Enjoy 😉';
    }

}
