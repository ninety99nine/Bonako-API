<?php

namespace App\Http\Requests\Models\MobileVerification;

use App\Traits\Base\BaseTrait;
use App\Models\MobileVerification;
use Illuminate\Foundation\Http\FormRequest;

class CreateMobileVerificationRequest extends FormRequest
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
            'return' => ['sometimes', 'boolean'],
            'mobile_number' => ['bail', 'required', 'phone'],
            'code' => ['bail', 'sometimes', 'integer', 'min:'.MobileVerification::CODE_CHARACTERS]
        ];
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
