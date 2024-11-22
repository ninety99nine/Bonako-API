<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Response;
use App\Traits\Base\BaseTrait;
use Illuminate\Http\JsonResponse;
use App\Repositories\UserRepository;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\User\ShowUsersRequest;
use App\Http\Requests\Models\User\CreateUserRequest;
use App\Http\Requests\Models\User\UpdateUserRequest;
use App\Http\Requests\Models\User\DeleteUsersRequest;
use App\Http\Requests\Models\User\ValidateCreateUserRequest;
use App\Http\Requests\Models\User\UploadProfilePhotoRequest;
use App\Http\Requests\Auth\VerifyMobileVerificationCodeRequest;
use App\Http\Requests\Models\User\ShowUserResourceTotalsRequest;
use App\Http\Requests\Models\User\SearchUserByMobileNumberRequest;

class UserController extends BaseController
{
    use BaseTrait;

    /**
     *  @var UserRepository
     */
    protected $repository;

    /**
     * UserController constructor.
     *
     * @param UserRepository $repository
     */
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show users.
     *
     * @param ShowUsersRequest $request
     * @return JsonResponse
     */
    public function showUsers(ShowUsersRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->showUsers());
    }
    /**
     * Create user.
     *
     * @param CreateUserRequest $request
     * @return JsonResponse
     */
    public function createUser(CreateUserRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->createUser($request->all()));
    }

    /**
     * Delete users.
     *
     * @param DeleteUsersRequest $request
     * @return JsonResponse
     */
    public function deleteUsers(DeleteUsersRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteUsers($request->all()));
    }

    /**
     * Validate create user.
     *
     * @param ValidateCreateUserRequest $request
     * @return JsonResponse
     */
    public function validateCreateUser(ValidateCreateUserRequest $_): JsonResponse
    {
        return $this->prepareOutput(null, Response::HTTP_OK);
    }

    /**
     * Search user by mobile number.
     *
     * @param SearchUserByMobileNumberRequest $request
     * @return JsonResponse
     */
    public function searchUserByMobileNumber(SearchUserByMobileNumberRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->searchUserByMobileNumber($request->input('mobile_number')));
    }

    /**
     * Show user.
     *
     * @return JsonResponse
     */
    public function showUser(): JsonResponse
    {
        return $this->prepareOutput($this->repository->showUser(request()->current_user));
    }

    /**
     * Update user.
     *
     * @param UpdateUserRequest $request
     * @return JsonResponse
     */
    public function updateUser(UpdateUserRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateUser(request()->current_user, $request->all()));
    }

    /**
     * Delete user.
     *
     * @return JsonResponse
     */
    public function deleteUser(): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteUser(request()->current_user));
    }

    /**
     * Generate user mobile verification code.
     *
     * @return JsonResponse
     */
    public function generateUserMobileVerificationCode(): JsonResponse
    {
        return $this->prepareOutput($this->repository->generateUserMobileVerificationCode(request()->current_user));
    }

    /**
     * Verify user mobile verification code.
     *
     * @param VerifyMobileVerificationCodeRequest $request
     * @return JsonResponse
     */
    public function verifyUserMobileVerificationCode(VerifyMobileVerificationCodeRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->verifyUserMobileVerificationCode(request()->current_user, $request->all()));
    }

    /**
     * Show user tokens.
     *
     * @return JsonResponse
     */
    public function showUserTokens(): JsonResponse
    {
        return $this->prepareOutput($this->repository->showUserTokens(request()->current_user));
    }

    /**
     * Logout user.
     *
     * @param LogoutRequest $request
     * @return JsonResponse
     */
    public function logoutUser(LogoutRequest $request)
    {
        return $this->prepareOutput($this->repository->logoutUser(request()->current_user, $request->all()));
    }

    /**
     * Show user profile photo.
     *
     * @return JsonResponse
     */
    public function showUserProfilePhoto()
    {
        return $this->prepareOutput($this->repository->showUserProfilePhoto(request()->current_user));
    }

    /**
     * Upload user profile photo.
     *
     * @return JsonResponse
     */
    public function uploadUserProfilePhoto(UploadProfilePhotoRequest $_)
    {
        return $this->prepareOutput($this->repository->uploadUserProfilePhoto(request()->current_user));
    }

    /**
     * Delete user profile photo.
     *
     * @return JsonResponse
     */
    public function deleteUserProfilePhoto()
    {
        return $this->prepareOutput($this->repository->deleteUserProfilePhoto(request()->current_user));
    }

    /**
     * Delete user profile photo.
     *
     * @return JsonResponse
     */
    public function showUserAiAssistant()
    {
        return $this->prepareOutput($this->repository->showUserAiAssistant(request()->current_user));
    }

    /**
     * Show user resource totals.
     *
     * @param ShowUserResourceTotalsRequest $request
     * @return JsonResponse
     */
    public function showUserResourceTotals(ShowUserResourceTotalsRequest $request)
    {
        $filter = collect(explode(',', $request->input('filter')))->map(fn($field) => Str::camel(trim($field)))->filter()->toArray();
        return $this->prepareOutput($this->repository->showUserResourceTotals(request()->current_user, $filter));
    }
}
