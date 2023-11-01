<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Response;
use App\Repositories\AuthRepository;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\AccountExistsRequest;
use App\Http\Requests\Models\User\CreateUserRequest;
use App\Http\Requests\Models\User\UpdateUserRequest;
use App\Http\Requests\Auth\ValidateResetPasswordRequest;
use App\Http\Requests\Auth\AcceptTermsAndConditionsRequest;
use App\Http\Requests\Models\User\ValidateCreateUserRequest;
use App\Http\Requests\Auth\ShowMobileVerificationCodeRequest;
use App\Http\Requests\Auth\VerifyMobileVerificationCodeRequest;
use App\Http\Requests\Auth\GenerateMobileVerificationCodeRequest;

class AuthController extends BaseController
{
    /**
     *  @var AuthRepository
     */
    protected $repository;

    public function login(LoginRequest $loginRequest)
    {
        return response($this->repository->login($loginRequest), Response::HTTP_OK);
    }

    public function register(CreateUserRequest $createUserRequest)
    {
        return response()->json($this->repository->register($createUserRequest), Response::HTTP_CREATED);
    }

    public function validateRegister(ValidateCreateUserRequest $_)
    {
        return response(null, Response::HTTP_OK);
    }

    public function accountExists(AccountExistsRequest $accountExistsRequest)
    {
        return response($this->repository->accountExists($accountExistsRequest), Response::HTTP_OK);
    }

    public function resetPassword(ResetPasswordRequest $resetPasswordRequest)
    {
        return response($this->repository->resetPassword($resetPasswordRequest), Response::HTTP_OK);
    }

    public function validateResetPassword(ValidateResetPasswordRequest $_)
    {
        return response(null, Response::HTTP_OK);
    }

    public function showMobileVerificationCode(ShowMobileVerificationCodeRequest $showMobileVerificationCodeRequest)
    {
        return response($this->repository->showMobileVerificationCode($showMobileVerificationCodeRequest), Response::HTTP_OK);
    }

    public function verifyMobileVerificationCode(VerifyMobileVerificationCodeRequest $verifyMobileVerificationCodeRequest)
    {
        return response($this->repository->verifyMobileVerificationCode($verifyMobileVerificationCodeRequest), Response::HTTP_OK);
    }

    public function generateMobileVerificationCode(GenerateMobileVerificationCodeRequest $generateMobileVerificationCodeRequest)
    {
        return response($this->repository->generateMobileVerificationCode($generateMobileVerificationCodeRequest), Response::HTTP_OK);
    }
}
