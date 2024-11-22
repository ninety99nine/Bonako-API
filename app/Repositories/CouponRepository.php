<?php

namespace App\Repositories;

use App\Models\Store;
use App\Models\Coupon;
use App\Traits\AuthTrait;
use App\Traits\Base\BaseTrait;
use Illuminate\Support\Collection;
use App\Services\Filter\FilterService;
use App\Http\Resources\CouponResources;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class CouponRepository extends BaseRepository
{
    use AuthTrait, BaseTrait;

    /**
     * Show coupons.
     *
     * @param Store|string|null $storeId
     * @return CouponResources|array
     */
    public function showCoupons(Store|string|null $storeId = null): CouponResources|array
    {
        if($this->getQuery() == null) {
            if(is_null($storeId)) {
                if(!$this->isAuthourized()) return ['message' => 'You do not have permission to show coupons'];
                $this->setQuery(Coupon::latest());
            }else{
                $store = $storeId instanceof Store ? $storeId : Store::find($storeId);
                if($store) {
                    $isAuthourized = $this->isAuthourized() || $this->getStoreRepository()->checkIfAssociatedAsStoreCreatorOrAdmin($store);
                    if(!$isAuthourized) return ['message' => 'You do not have permission to show coupons'];
                    $this->setQuery($store->coupons()->latest());
                }else{
                    return ['message' => 'This store does not exist'];
                }
            }
        }

        return $this->applyFiltersOnQuery()->getOrCountResources();
    }

    /**
     * Create coupon.
     *
     * @param array $data
     * @return Coupon|array
     */
    public function createCoupon(array $data): Coupon|array
    {
        $storeId = $data['store_id'];
        $store = Store::find($storeId);

        if($store) {
            $isAuthourized = $this->isAuthourized() || $this->getStoreRepository()->checkIfAssociatedAsStoreCreatorOrAdmin($store);
            if(!$isAuthourized) return ['created' => false, 'message' => 'You do not have permission to create coupons'];
        }else{
            return ['created' => false, 'message' => 'This store does not exist'];
        }

        $data = array_merge($data, [
            'currency' => $store->currency,
            'store_id' => $storeId
        ]);

        $coupon = Coupon::create($data);
        return $this->showCreatedResource($coupon);
    }

    /**
     * Delete coupons.
     *
     * @param array $data
     * @return array
     */
    public function deleteCoupons(array $data): array
    {
        $storeId = $data['store_id'];

        if(is_null($storeId)) {
            if(!$this->isAuthourized()) return ['deleted' => false, 'message' => 'You do not have permission to delete coupons'];
            $this->setQuery(Coupon::query());
        }else{

            $store = Store::find($storeId);

            if($store) {
                $isAuthourized = $this->isAuthourized() || $this->getStoreRepository()->checkIfAssociatedAsStoreCreatorOrAdmin($store);
                if(!$isAuthourized) return ['deleted' => false, 'message' => 'You do not have permission to delete coupons'];
                $this->setQuery($store->coupons());
            }else{
                return ['deleted' => false, 'message' => 'This store does not exist'];
            }

        }

        $couponIds = $data['coupon_ids'];
        $coupons = $this->getCouponsByIds($couponIds);

        if($totalCoupons = $coupons->count()) {

            foreach($coupons as $coupon) {
                $coupon->delete();
            }

            return ['deleted' => true, 'message' => $totalCoupons . ($totalCoupons == 1 ? ' coupon': ' coupons') . ' deleted'];

        }else{
            return ['deleted' => false, 'message' => 'No coupons deleted'];
        }
    }

    /**
     * Show coupon.
     *
     * @param string $couponId
     * @return Coupon|array|null
     */
    public function showCoupon(string $couponId): Coupon|array|null
    {
        $coupon = $this->setQuery(Coupon::with(['store'])->whereId($couponId))->applyEagerLoadingOnQuery()->getQuery()->first();

        if($coupon) {
            $store = $coupon->store;
            if($store) {
                $isAuthourized = $this->isAuthourized() || $this->getStoreRepository()->checkIfAssociatedAsStoreCreatorOrAdmin($store);
                if(!$isAuthourized) return ['message' => 'You do not have permission to show coupon'];
                if(!$this->checkIfHasRelationOnRequest('store')) $coupon->unsetRelation('store');
            }else{
                return ['message' => 'This store does not exist'];
            }
        }

        return $this->showResourceExistence($coupon);
    }

    /**
     * Update coupon.
     *
     * @param string $couponId
     * @param array $data
     * @return Coupon|array
     */
    public function updateCoupon(string $couponId, array $data): Coupon|array
    {
        $coupon = Coupon::with(['store'])->find($couponId);

        if($coupon) {
            $store = $coupon->store;
            if($store) {
                $isAuthourized = $this->isAuthourized() || $this->getStoreRepository()->checkIfAssociatedAsStoreCreatorOrAdmin($store);
                if(!$isAuthourized) return ['updated' => false, 'message' => 'You do not have permission to update coupon'];
            }else{
                return ['updated' => false, 'message' => 'This store does not exist'];
            }

            $coupon->update($data);
            return $this->showUpdatedResource($coupon);

        }else{
            return ['updated' => false, 'message' => 'This coupon does not exist'];
        }
    }

    /**
     * Delete coupon.
     *
     * @param string $couponId
     * @return array
     */
    public function deleteCoupon(string $couponId): array
    {
        $coupon = Coupon::with(['store'])->find($couponId);

        if($coupon) {
            $store = $coupon->store;
            if($store) {
                $isAuthourized = $this->isAuthourized() || $this->getStoreRepository()->checkIfAssociatedAsStoreCreatorOrAdmin($store);
                if(!$isAuthourized) return ['deleted' => false, 'message' => 'You do not have permission to delete coupon'];
            }else{
                return ['deleted' => false, 'message' => 'This store does not exist'];
            }

            $deleted = $coupon->delete();

            if ($deleted) {
                return ['deleted' => true, 'message' => 'Coupon deleted'];
            }else{
                return ['deleted' => false, 'message' => 'Coupon delete unsuccessful'];
            }
        }else{
            return ['deleted' => false, 'message' => 'This coupon does not exist'];
        }
    }

    /***********************************************
     *             MISCELLANEOUS METHODS           *
     **********************************************/

    /**
     * Query coupon by ID.
     *
     * @param Coupon|string $couponId
     * @param array $relationships
     * @return Builder|Relation
     */
    public function queryCouponById(Coupon|string $couponId, array $relationships = []): Builder|Relation
    {
        return $this->query->where('coupons.id', $couponId)->with($relationships);
    }

    /**
     * Get coupon by ID.
     *
     * @param Coupon|string $couponId
     * @param array $relationships
     * @return Coupon|null
     */
    public function getCouponById(Coupon|string $couponId, array $relationships = []): Coupon|null
    {
        return $this->queryCouponById($couponId, $relationships)->first();
    }

    /**
     * Query coupons by IDs.
     *
     * @param array<string> $couponId
     * @param string $relationships
     * @return Builder|Relation
     */
    public function queryCouponsByIds($couponIds): Builder|Relation
    {
        return $this->query->whereIn('coupons.id', $couponIds);
    }

    /**
     * Get coupons by IDs.
     *
     * @param array<string> $couponId
     * @param string $relationships
     * @return Collection
     */
    public function getCouponsByIds($couponIds): Collection
    {
        return $this->queryCouponsByIds($couponIds)->get();
    }
}
