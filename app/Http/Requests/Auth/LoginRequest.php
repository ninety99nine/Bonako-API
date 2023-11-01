<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use App\Services\Ussd\UssdService;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
        $mobileNumber = request()->input('mobile_number');
        $hasMobileNumber = request()->filled('mobile_number');

        $requestIsFromUssdServer = $ussdService->verifyIfRequestFromUssdServer();
        $hasPasswordSetForAccount = $hasMobileNumber ? (User::searchMobileNumber($mobileNumber)->first()->password ?? false) : false;

        return [
            'mobile_number' => ['bail', 'required', 'string', 'starts_with:267', 'regex:/^[0-9]+$/', 'size:11', 'exists:users,mobile_number'],
            'password' => array_merge(
                //  If the request is not from the ussd server then the password is required
                ['bail', Rule::requiredIf(!$requestIsFromUssdServer), 'string', 'min:'.User::PASSWORD_MIN_CHARACTERS],
                //  If the user provided a mobile number, and does have a password set for the
                //  account matching the mobile number, then the password given must be confirmed.
                ($hasMobileNumber && !$hasPasswordSetForAccount) ? ['confirmed'] : []
            ),
            //  If the user provided a mobile number, but does not have a password set for the
            //  account matching the mobile number, then the verification code is required
            'verification_code' => ['bail', Rule::requiredIf(!$requestIsFromUssdServer && !$hasPasswordSetForAccount), 'string', 'size:6', 'regex:/^[0-9]+$/',
                Rule::exists('mobile_verifications', 'code')->where('mobile_number', request()->input('mobile_number')),
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        $mobileNumberWithoutExtension = $this->convertToMobileNumberFormat(request()->input('mobile_number'))->withoutExtension;
        $hasPasswordConfirmation = request()->filled('password_confirmation');

        return [
            'password.confirmed'=> $hasPasswordConfirmation ? 'The password confirmation does not match' : 'The password confirmation field is required since the account does not have a password set.',
            'verification_code.required' => 'The verification code field is required since the account does not have a password set.',
            'verification_code.regex' => 'The verification code must only contain numbers',
            'verification_code.exists' => 'The verification code is not valid.',
            'mobile_number.regex' => 'The mobile number must only contain numbers',
            'mobile_number.exists' => 'The account using the mobile number '.$mobileNumberWithoutExtension.' does not exist.',
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
