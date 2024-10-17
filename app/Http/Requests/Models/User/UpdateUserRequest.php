<?php

namespace App\Http\Requests\Models\User;

use App\Models\User;
use Illuminate\Validation\Rule;
use App\Models\MobileVerification;
use App\Services\Ussd\UssdService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        /**
         *  @var User $authUser
         */
        $authUser = request()->auth_user;
        $currentUser = request()->current_user;
        $authUserIsSuperAdmin = $authUser->isSuperAdmin();
        $requestFromUssdServer = UssdService::verifyIfRequestFromUssdServer();

        $alreadyHasPassword = !empty($currentUser->password);
        $uniqueMobileNumber = Rule::unique('users')->ignore($currentUser->id);
        $passwordHasBeenChanged = request()->filled('password') ? !(Hash::check(request()->input('password'), $currentUser->password)) : false;
        $mobileNumberHasBeenChanged = request()->filled('mobile_number') ? (
            request()->input('mobile_number') != $currentUser->mobile_number->formatInternational()
        ) : false;

        return [
            'first_name' => ['bail', 'sometimes', 'required', 'string', 'min:'.User::FIRST_NAME_MIN_CHARACTERS, 'max:'.User::FIRST_NAME_MAX_CHARACTERS],
            'last_name' => ['bail', 'sometimes', 'required', 'string', 'min:'.User::LAST_NAME_MIN_CHARACTERS, 'max:'.User::LAST_NAME_MAX_CHARACTERS],
            'about_me' => ['bail', 'nullable', 'string', 'min:'.User::ABOUT_ME_MIN_CHARACTERS, 'max:'.User::ABOUT_ME_MAX_CHARACTERS],
            'mobile_number' => [
                'bail', 'sometimes', 'required', 'phone', $uniqueMobileNumber
            ],

            /**
             *  When updating the user's password, the user must confirm the new
             *  password that they want to change to. If the request is performed
             *  by the Super Admin, then we do not need to confirm any password
             *  to set a new password.
             */
            'password' => array_merge(
                ['bail', 'sometimes', 'required', 'string', 'min:'.User::PASSWORD_MIN_CHARACTERS],
                $authUserIsSuperAdmin ? [] : ['confirmed']
            ),

            /**
             *  When updating the user's password, the user must confirm their
             *  current password before setting their new password. If the
             *  request is performed by the Super Admin, then we do not
             *  need to confirm the current password. Validation rules
             *  on the current password are applied if the user
             *  already has a password set.
             */
            'current_password' => $alreadyHasPassword && $passwordHasBeenChanged && !($authUserIsSuperAdmin || $requestFromUssdServer) ? [
                'bail', 'required', 'string', 'min:'.User::PASSWORD_MIN_CHARACTERS, 'current_password:sanctum'
            ] : [],

            /**
             *  When updating the user's mobile number, the user must provide the
             *  verification code of the new mobile number that they would like
             *  to change to. If the request is performed by the Super Admin,
             *  then we do not need to provide a verification code to verify
             *  this mobile number.
             */
            'verification_code' => $mobileNumberHasBeenChanged && !$authUserIsSuperAdmin ? [
                'bail', 'required', 'integer', 'min:'.MobileVerification::CODE_CHARACTERS,
                Rule::exists('mobile_verifications', 'code')->where('mobile_number', request()->input('mobile_number')),
            ] : []
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'verification_code.required' => 'The verification code is required to verify ownership of the mobile number ' . request()->input('mobile_number'),
            'verification_code.exists' => 'The verification code is not valid.',
            'verification_code.regex' => 'The verification code must only contain numbers',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [];
    }
}
