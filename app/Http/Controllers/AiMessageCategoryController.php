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
        return $this->prepareOutput($this->repository->showAiMessageCategories());
    }

    public function showAiMessageCategory(AiMessageCategory $aiMessageCategory)
    {
        return $this->prepareOutput($this->setModel($aiMessageCategory)->showAiMessageCategory());
    }
}
