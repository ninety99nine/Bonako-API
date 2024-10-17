<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\CouponRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\Coupon\ShowCouponsRequest;
use App\Http\Requests\Models\Coupon\CreateCouponRequest;
use App\Http\Requests\Models\Coupon\UpdateCouponRequest;
use App\Http\Requests\Models\Coupon\DeleteCouponsRequest;

class CouponController extends BaseController
{
    /**
     *  @var CouponRepository
     */
    protected $repository;

    /**
     * CouponController constructor.
     *
     * @param CouponRepository $repository
     */
    public function __construct(CouponRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show coupons.
     *
     * @param ShowCouponsRequest $request
     * @param string|null $storeId
     * @return JsonResponse
     */
    public function showCoupons(ShowCouponsRequest $request, string|null $storeId = null): JsonResponse
    {
        return $this->prepareOutput($this->repository->showCoupons($storeId ?? $request->input('store_id')));
    }

    /**
     * Create coupon.
     *
     * @param CreateCouponRequest $request
     * @return JsonResponse
     */
    public function createCoupon(CreateCouponRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->createCoupon($request->all()));
    }

    /**
     * Delete coupons.
     *
     * @param DeleteCouponsRequest $request
     * @return JsonResponse
     */
    public function deleteCoupons(DeleteCouponsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteCoupons($request->all()));
    }

    /**
     * Show coupon.
     *
     * @param string $couponId
     * @return JsonResponse
     */
    public function showCoupon(string $couponId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showCoupon($couponId));
    }

    /**
     * Update coupon.
     *
     * @param UpdateCouponRequest $request
     * @param string $couponId
     * @return JsonResponse
     */
    public function updateCoupon(UpdateCouponRequest $request, string $couponId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateCoupon($couponId, $request->all()));
    }

    /**
     * Delete coupon.
     *
     * @param string $couponId
     * @return JsonResponse
     */
    public function deleteCoupon(string $couponId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteCoupon($couponId));
    }
}
