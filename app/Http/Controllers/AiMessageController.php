<?php

namespace App\Http\Controllers;

use App\Models\AiMessage;
use Illuminate\Http\Response;
use App\Repositories\AiMessageRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\AiMessage\CreateAiMessageRequest;
use App\Http\Requests\Models\AiMessage\UpdateAiMessageRequest;

class AiMessageController extends BaseController
{
    /**
     *  @var AiMessageRepository
     */
    protected $repository;
}
