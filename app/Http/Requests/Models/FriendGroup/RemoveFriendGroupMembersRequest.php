<?php

namespace App\Http\Requests\Models\FriendGroup;

use Illuminate\Foundation\Http\FormRequest;

class RemoveFriendGroupMembersRequest extends FormRequest
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
     *  We want to modify the request input before validating
     *
     *  Reference: https://laracasts.com/discuss/channels/requests/modify-request-input-value-before-validation
     */
    public function getValidatorInstance()
    {
        //  Make sure that the "mobile_numbers" is an array if provided
        if($this->has('mobile_numbers') && is_string($this->request->all()['mobile_numbers'])) {
            $this->merge([
                'mobile_numbers' => json_decode($this->request->all()['mobile_numbers'])
            ]);
        }

        return parent::getValidatorInstance();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'mobile_numbers' => ['required', 'array'],
            'mobile_numbers.*' => ['bail', 'string', 'distinct', 'starts_with:267', 'regex:/^[0-9]+$/', 'size:11'],
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
            'mobile_numbers.*.regex' => 'The mobile number must only contain numbers',
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
            'mobile_numbers.*' => 'mobile number'
        ];
    }
}
