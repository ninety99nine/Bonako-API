<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Base\BaseController;
use App\Repositories\MobileVerificationRepository;
use App\Http\Requests\Models\MobileVerification\ShowMobileVerificationsRequest;
use App\Http\Requests\Models\MobileVerification\CreateMobileVerificationRequest;
use App\Http\Requests\Models\MobileVerification\UpdateMobileVerificationRequest;
use App\Http\Requests\Models\MobileVerification\DeleteMobileVerificationsRequest;

class MobileVerificationController extends BaseController
{
    /**
     *  @var MobileVerificationRepository
     */
    protected $repository;

    /**
     * MobileVerificationController constructor.
     *
     * @param MobileVerificationRepository $repository
     */
    public function __construct(MobileVerificationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show mobile verifications.
     *
     * @param ShowMobileVerificationRequest $request
     * @return JsonResponse
     */
    public function showMobileVerifications(ShowMobileVerificationsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->showMobileVerifications($request->all()));
    }

    /**
     * Create mobile verification.
     *
     * @param CreateMobileVerificationRequest $request
     * @return JsonResponse
     */
    public function createMobileVerification(CreateMobileVerificationRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->createMobileVerification($request->all()));
    }

    /**
     * Delete mobile verifications.
     *
     * @param DeleteMobileVerificationsRequest $request
     * @return JsonResponse
     */
    public function deleteMobileVerifications(DeleteMobileVerificationsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteMobileVerifications($request->input('mobile_verification_ids')));
    }

    /**
     * Show mobile verification.
     *
     * @param string $mobileVerificationId
     * @return JsonResponse
     */
    public function showMobileVerification(string $mobileVerificationId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showMobileVerification($mobileVerificationId));
    }

    /**
     * Update mobile verification.
     *
     * @param UpdateMobileVerificationRequest $request
     * @param string $mobileVerificationId
     * @return JsonResponse
     */
    public function updateMobileVerification(UpdateMobileVerificationRequest $request, string $mobileVerificationId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateMobileVerification($mobileVerificationId, $request->all()));
    }

    /**
     * Delete mobile verification.
     *
     * @param string $mobileVerificationId
     * @return JsonResponse
     */
    public function deleteMobileVerification(string $mobileVerificationId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteMobileVerification($mobileVerificationId));
    }
}
