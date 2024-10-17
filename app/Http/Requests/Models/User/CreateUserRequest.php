<?php

namespace App\Http\Requests\Models\User;

use App\Models\User;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use App\Models\MobileVerification;
use App\Services\Ussd\UssdService;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    use BaseTrait;

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
    public function rules(UssdService $ussdService)
    {
        /**
         *  @var User $user
         */
        $authUserIsSuperAdmin = ($user = request()->auth_user) ? $user->isSuperAdmin() : false;
        $requestIsFromUssdServer = $ussdService->verifyIfRequestFromUssdServer();

        return [
            'first_name' => ['bail', 'required', 'string', 'min:'.User::FIRST_NAME_MIN_CHARACTERS, 'max:'.User::FIRST_NAME_MAX_CHARACTERS],
            'last_name' => ['bail', 'required', 'string', 'min:'.User::LAST_NAME_MIN_CHARACTERS, 'max:'.User::LAST_NAME_MAX_CHARACTERS],
            'about_me' => ['bail', 'nullable', 'string', 'min:'.User::ABOUT_ME_MIN_CHARACTERS, 'max:'.User::ABOUT_ME_MAX_CHARACTERS],
            'mobile_number' => ['bail', 'required', 'phone', 'unique:users,mobile_number'],
            /**
             *  Since the creation of an account can be done by any user creating their
             *  own profile, or by a Super Admin creating a profile on be-half of other
             *  users, we need to check if we must require a password and verification
             *  code from the user. We require the password and verification code if:
             *
             *  (1) The request is not from the USSD server (e.g this action is being
             *  performed by a person either using the web-app or the mobile-app).
             *  But if the request is coming from the USSD server e.g a customer
             *  registering a new account, then we dont need the password and
             *  verification code (since they are not required to use a
             *  web-app or mobile-app to consume the service)
             *
             *  and ...
             *
             *  (2) The person performing the request is not a Super Admin.
             *
             *  If both these cases pass, then we must require the user to provide
             *  a password and a verification code to confirm the ownership of the
             *  mobile number before creating this account.
             *
             *  NOTE: If the request is performed by the Super Admin, then we do
             *  not need to confirm the password.
             */
            'password' => array_merge(
                ['bail', Rule::requiredIf(!$requestIsFromUssdServer && !$authUserIsSuperAdmin), 'string', 'min:'.User::PASSWORD_MIN_CHARACTERS, ],
                $authUserIsSuperAdmin ? [] : ['confirmed']
            ),
            'verification_code' => !$requestIsFromUssdServer && !$authUserIsSuperAdmin ? [
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
