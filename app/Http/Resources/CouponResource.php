<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class CouponResource extends BaseResource
{
    /**
     *  Check if this coupon is being requested by a team member
     *  who has the permissions to manage orders
     *
     *  Note that an coupon is retrieved from a store, in
     *  which case the "user_store_association" will exist
     *
     *  @return bool
     */
    private function canManageCoupons() {
        return request()->store->user_store_association->can_manage_coupons;
    }

    /**
     *  Check if this order is being requested by a user that is allowed
     *  to see more sensitive information regarding this order.
     *
     *  @return bool
     */
    private function viewingPrivately() {

        $isSuperAdmin = $this->isSuperAdmin;
        $canManageCoupons = $this->canManageCoupons();

        return $isSuperAdmin || $canManageCoupons;
    }

    /**
     *  Check if this order is being requested by a user that is not allowed
     *  to see more sensitive information regarding this order.
     *
     *  @return bool
     */
    private function viewingPublicly() {
        return $this->viewingPrivately() == false;
    }

    public function toArray($request)
    {
        /**
         *  Viewing as Public User
         *
         *  If we are veiwing as the general public then limit the information we share.
         *  Usually we just want the basic coupon details, nothing that would expose
         *  sensitive coupon information such as coupon codes. Only the store Team
         *  Members can see those details.
         */
        //  if( $this->viewingPublicly() ) {

            //  Overide and apply custom fields
            //  $this->customExcludeFields = ['code', 'store_id', 'user_id'];

        //}

        return $this->transformedStructure();

    }

    public function setLinks()
    {
        $coupon = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.coupon', route('show.coupon', ['couponId' => $coupon->id])),
            new ResourceLink('update.coupon', route('update.coupon', ['couponId' => $coupon->id])),
            new ResourceLink('delete.coupon', route('delete.coupon', ['couponId' => $coupon->id])),
        ];
    }
}
