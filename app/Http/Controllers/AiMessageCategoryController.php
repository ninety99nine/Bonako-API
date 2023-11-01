<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use App\Models\AiMessageCategory;
use App\Http\Controllers\Base\BaseController;
use App\Repositories\AiMessageCategoryRepository;

class AiMessageCategoryController extends BaseController
{
    /**
     *  @var AiMessageCategoryRepository
     */
    protected $repository;

    public function showAiMessageCategories()
    {
        return response($this->repository->showAiMessageCategories()->transform(), Response::HTTP_OK);
    }

    public function showAiMessageCategory(AiMessageCategory $aiMessageCategory)
    {
        return response($this->repository->setModel($aiMessageCategory)->showAiMessageCategory()->transform(), Response::HTTP_OK);
    }
}
