<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\Base\BaseTrait;
use App\Repositories\BaseRepository;

class AddressRepository extends BaseRepository
{
    use BaseTrait;

    /**
     *  Deleting addresses does not require confirmation.
     */
    protected $requiresConfirmationBeforeDelete = false;
}
