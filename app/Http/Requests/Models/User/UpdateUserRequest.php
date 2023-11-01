<?php

namespace App\Http\Requests\Models\User;

use App\Models\User;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use App\Services\Ussd\UssdService;
use App\Models\MobileVerification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    use BaseTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //  Everyone is authorized to make this request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(UssdService $ussdService)
    {
        /**
         *  @var User $authUser
         */
        $authUser = auth()->user();
        $user = $this->chooseUser();
        $authUserIsSuperAdmin = $authUser->isSuperAdmin();

        $alreadyHasPassword = !empty($user->password);
        $uniqueMobileNumber = Rule::unique('users')->ignore($user->id);
        $passwordHasBeenChanged = request()->filled('password') ? !(Hash::check(request()->input('password'), $user->password)) : false;
        $mobileNumberHasBeenChanged = request()->filled('mobile_number') ? (
            request()->input('mobile_number') != $user->mobile_number->withExtension
        ) : false;

        return [
            'first_name' => ['bail', 'sometimes', 'required', 'string', 'min:'.User::FIRST_NAME_MIN_CHARACTERS, 'max:'.User::FIRST_NAME_MAX_CHARACTERS],
            'last_name' => ['bail', 'sometimes', 'required', 'string', 'min:'.User::LAST_NAME_MIN_CHARACTERS, 'max:'.User::LAST_NAME_MAX_CHARACTERS],
            'mobile_number' => [
                'bail', 'sometimes', 'required', 'string', 'starts_with:267',
                'regex:/^[0-9]+$/', 'size:11', $uniqueMobileNumber
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
            'current_password' => $alreadyHasPassword && $passwordHasBeenChanged && !$authUserIsSuperAdmin ? [
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
                'bail', 'required', 'string', 'size:'.MobileVerification::CODE_CHARACTERS, 'regex:/^[0-9]+$/',
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
            'mobile_number.regex' => 'The mobile number must only contain numbers',
            'mobile_number.unique' => 'An account using the mobile number '.request()->input('mobile_number').' already exists.',
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
        return [

        ];
    }
}
