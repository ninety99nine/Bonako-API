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
        return $this->prepareOutput($this->setModel($coupon));
    }

    public function update(UpdateCouponRequest $request, Store $store, Coupon $coupon)
    {
        return $this->prepareOutput($this->setModel($coupon)->update($request));
    }

    public function confirmDelete(Store $store, Coupon $coupon)
    {
        return $this->prepareOutput($this->setModel($coupon)->generateDeleteConfirmationCode());
    }

    public function delete(Store $store, Coupon $coupon)
    {
        return $this->prepareOutput($this->setModel($coupon)->delete());
    }
}
