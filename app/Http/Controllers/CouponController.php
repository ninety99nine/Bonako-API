<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Coupon;
use Illuminate\Http\Response;
use App\Repositories\CouponRepository;
use App\Http\Requests\Models\DeleteRequest;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\Coupon\UpdateCouponRequest;

class CouponController extends BaseController
{
    /**
     *  @var CouponRepository
     */
    protected $repository;

    public function show(Store $store, Coupon $coupon)
    {
        return response($this->repository->setModel($coupon)->transform(), Response::HTTP_OK);
    }

    public function update(UpdateCouponRequest $request, Store $store, Coupon $coupon)
    {
        return response($this->repository->setModel($coupon)->update($request)->transform(), Response::HTTP_OK);
    }

    public function confirmDelete(Store $store, Coupon $coupon)
    {
        return response($this->repository->setModel($coupon)->generateDeleteConfirmationCode(), Response::HTTP_OK);
    }

    public function delete(Store $store, Coupon $coupon)
    {
        return response($this->repository->setModel($coupon)->delete(), Response::HTTP_OK);
    }
}
