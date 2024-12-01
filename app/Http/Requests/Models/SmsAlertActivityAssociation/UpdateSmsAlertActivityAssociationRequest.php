<?php

namespace App\Http\Requests\Models\SmsAlertActivityAssociation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSmsAlertActivityAssociationRequest extends FormRequest
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

            //  Make sure that the "store_ids" is an array if provided
            if($this->has('store_ids') && is_string($this->request->all()['store_ids'])) {
                $this->merge([
                    'store_ids' => json_decode($this->request->all()['store_ids'])
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
            'enabled' => ['bail', 'sometimes', 'boolean'],
            'store_ids' => ['bail', 'array'],
            'store_ids.*' => ['bail', 'required', 'uuid', 'distinct']
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
