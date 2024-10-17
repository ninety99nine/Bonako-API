<?php

namespace App\Http\Requests\Models\User;

use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use App\Services\Ussd\UssdService;
use Illuminate\Foundation\Http\FormRequest;

class ValidateCreateUserRequest extends FormRequest
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
        return collect( (new CreateUserRequest)->rules($ussdService) )->except(['verification_code'])->toArray();
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return (new CreateUserRequest)->messages();
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
