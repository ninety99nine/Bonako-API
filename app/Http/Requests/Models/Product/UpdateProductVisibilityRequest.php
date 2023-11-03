<?php

namespace App\Http\Requests\Models\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductVisibilityRequest extends FormRequest
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
        //  Make sure that the "visibility" is an array if provided
        if($this->request->has('visibility') && is_string($this->request->all()['visibility'])) {
            $this->merge([
                'visibility' => json_decode($this->request->all()['visibility'])
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
            'visibility' => ['required', 'array'],
            'visibility.*.id' => ['bail', 'required', 'integer', 'numeric', 'min:1', 'distinct'],
            'visibility.*.visible' => ['bail', 'required', 'boolean'],
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
