<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\SmsMessageRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\SmsMessage\ShowSmsMessagesRequest;
use App\Http\Requests\Models\SmsMessage\CreateSmsMessageRequest;
use App\Http\Requests\Models\SmsMessage\UpdateSmsMessageRequest;
use App\Http\Requests\Models\SmsMessage\DeleteSmsMessagesRequest;

class SmsMessageController extends BaseController
{
    /**
     *  @var SmsMessageRepository
     */
    protected $repository;

    /**
     * SmsMessageController constructor.
     *
     * @param SmsMessageRepository $repository
     */
    public function __construct(SmsMessageRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show SMS messages.
     *
     * @param ShowSmsMessageRequest $request
     * @return JsonResponse
     */
    public function showSmsMessages(ShowSmsMessagesRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->showSmsMessages($request->all()));
    }

    /**
     * Create SMS message.
     *
     * @param CreateSmsMessageRequest $request
     * @return JsonResponse
     */
    public function createSmsMessage(CreateSmsMessageRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->createSmsMessage($request->all()));
    }

    /**
     * Delete SMS messages.
     *
     * @param DeleteSmsMessagesRequest $request
     * @return JsonResponse
     */
    public function deleteSmsMessages(DeleteSmsMessagesRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteSmsMessages($request->input('sms_message_ids')));
    }

    /**
     * Show SMS message.
     *
     * @param string $smsMessageId
     * @return JsonResponse
     */
    public function showSmsMessage(string $smsMessageId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showSmsMessage($smsMessageId));
    }

    /**
     * Update SMS message.
     *
     * @param UpdateSmsMessageRequest $request
     * @param string $smsMessageId
     * @return JsonResponse
     */
    public function updateSmsMessage(UpdateSmsMessageRequest $request, string $smsMessageId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateSmsMessage($smsMessageId, $request->all()));
    }

    /**
     * Delete SMS message.
     *
     * @param string $smsMessageId
     * @return JsonResponse
     */
    public function deleteSmsMessage(string $smsMessageId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteSmsMessage($smsMessageId));
    }
}
