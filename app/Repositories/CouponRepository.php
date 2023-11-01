<?php

namespace App\Repositories;

use App\Repositories\BaseRepository;

class CouponRepository extends BaseRepository
{
    /**
     *  Deleting coupons does not require confirmation.
     */
    protected $requiresConfirmationBeforeDelete = false;
}
