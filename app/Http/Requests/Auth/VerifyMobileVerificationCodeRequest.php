<?php

namespace App\Http\Requests\Auth;

use App\Traits\AuthTrait;
use App\Models\MobileVerification;
use Illuminate\Foundation\Http\FormRequest;

class VerifyMobileVerificationCodeRequest extends FormRequest
{
    use AuthTrait;

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
        return array_merge(
            $this->hasAuthUser() ? [] : ['mobile_number' => ['bail', 'required', 'string', 'phone']],
            ['verification_code' => ['bail', 'required', 'integer', 'min:'.MobileVerification::CODE_CHARACTERS]
        ]);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [];
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
