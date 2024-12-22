<?php

namespace App\Http\Requests\Auth;

use App\Models\MobileVerification;
use App\Models\User;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Services\PhoneNumber\PhoneNumberService;

class ResetPasswordRequest extends FormRequest
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
    public function rules()
    {
        return [
            'mobile_number' => ['bail', 'required', 'string', 'phone', 'exists:users,mobile_number'],
            'verification_code' => ['bail', 'required', 'integer', 'min:'.MobileVerification::CODE_CHARACTERS,
                Rule::exists('mobile_verifications', 'code')->where('mobile_number', request()->input('mobile_number')),
            ],
            'password' => ['bail', 'required', 'string', 'min:'.User::PASSWORD_MIN_CHARACTERS, 'confirmed'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */

    public function messages()
    {
        $mobileNumber = PhoneNumberService::getNationalPhoneNumberWithoutSpaces(request()->input('mobile_number'));

        return [
            'verification_code.required' => 'The verification code is required to verify ownership of the mobile number ' . $mobileNumber,
            'verification_code.regex' => 'The verification code must only contain numbers',
            'verification_code.exists' => 'The verification code is not valid.',
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
