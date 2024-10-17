<?php

namespace App\Http\Requests\Models\Store;

use Illuminate\Foundation\Http\FormRequest;

class InviteFollowersRequest extends FormRequest
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
        try {

            //  Make sure that the "mobile_numbers" is an array if provided
            if($this->has('mobile_numbers') && is_string($this->request->all()['mobile_numbers'])) {
                $this->merge([
                    'mobile_numbers' => json_decode($this->request->all()['mobile_numbers'])
                ]);
            }

        } catch (\Throwable $th) {

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
            'mobile_numbers.*' => ['bail', 'distinct', 'phone'],
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
