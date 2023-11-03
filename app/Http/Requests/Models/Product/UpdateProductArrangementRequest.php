<?php

namespace App\Http\Requests\Models\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductArrangementRequest extends FormRequest
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
        //  Make sure that the "arrangement" is an array if provided
        if($this->request->has('arrangement') && is_string($this->request->all()['arrangement'])) {
            $this->merge([
                'arrangement' => json_decode($this->request->all()['arrangement'])
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
            'arrangement' => ['required', 'array'],
            'arrangement.*' => ['required', 'integer', 'min:1']
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
