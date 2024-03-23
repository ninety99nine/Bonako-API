<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use App\Repositories\ShortcodeRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\Shortcode\ShowShortcodeOwnerRequest;

class ShortcodeController extends BaseController
{
    /**
     *  @var ShortcodeRepository
     */
    protected $repository;

    public function showOwner(ShowShortcodeOwnerRequest $request)
    {
        return $this->prepareOutput($this->repository->showShortcodeOwner($request));
    }
}
