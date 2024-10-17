<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\CouponLineRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\CouponLine\ShowCouponLinesRequest;

class CouponLineController extends BaseController
{
    /**
     *  @var CouponLineRepository
     */
    protected $repository;

    /**
     * CouponLineController constructor.
     *
     * @param CouponLineRepository $repository
     */
    public function __construct(CouponLineRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show coupon lines.
     *
     * @param ShowCouponLinesRequest $request
     * @param string|null $storeId
     * @return JsonResponse
     */
    public function showCouponLines(ShowCouponLinesRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->showCouponLines());
    }

    /**
     * Show coupon line.
     *
     * @param string $couponLineId
     * @return JsonResponse
     */
    public function showCouponLine(string $couponLineId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showCouponLine($couponLineId));
    }
}
