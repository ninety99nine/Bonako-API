<?php

namespace App\Repositories;

use App\Traits\AuthTrait;
use App\Models\CouponLine;
use App\Traits\Base\BaseTrait;
use App\Repositories\BaseRepository;
use App\Services\Filter\FilterService;
use App\Http\Resources\CouponLineResources;

class CouponLineRepository extends BaseRepository
{
    use AuthTrait, BaseTrait;

    /**
     * Show coupon lines.
     *
     * @return CouponLineResources|array
     */
    public function showCouponLines(): CouponLineResources|array
    {
        if($this->getQuery() == null) {
            if(!$this->isAuthourized()) return ['message' => 'You do not have permission to show coupon lines'];
            $this->setQuery(CouponLine::query()->latest());
        }

        return $this->applyFiltersOnQuery()->getOrCountResources();
    }

    /**
     * Show coupon line.
     *
     * @param CouponLine|string|null $couponLineId
     * @return CouponLine|array|null
     */
    public function showCouponLine(CouponLine|string|null $couponLineId = null): CouponLine|array|null
    {
        if(($couponLine = $couponLineId) instanceof CouponLine) {
            $couponLine = $this->applyEagerLoadingOnModel($couponLine);
        }else {
            $query = $this->getQuery() ?? CouponLine::query();
            if($couponLineId) $query = $query->where('coupon_lines.id', $couponLineId);
            $this->setQuery($query)->applyEagerLoadingOnQuery();
            $couponLine = $this->query->first();
        }

        return $this->showResourceExistence($couponLine);
    }

    /***********************************************
     *            MISCELLANEOUS METHODS           *
     **********************************************/
}
