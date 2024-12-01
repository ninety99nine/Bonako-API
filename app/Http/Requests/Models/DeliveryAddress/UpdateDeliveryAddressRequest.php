<?php

namespace App\Http\Requests\Models\DeliveryAddress;

use App\Models\DeliveryAddress;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliveryAddressRequest extends FormRequest
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

            if($this->has('type')) $this->merge(['type' => strtolower($this->get('type'))]);

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
            'return' => ['sometimes', 'boolean'],
            'type' => ['bail', 'sometimes', 'nullable', Rule::in(DeliveryAddress::TYPES())],
            'address_line' => ['bail', 'required', 'string', 'max:' . DeliveryAddress::ADDRESS_MAX_CHARACTERS],
            'address_line2' => ['bail', 'sometimes', 'nullable', 'string', 'max:' . DeliveryAddress::ADDRESS2_MAX_CHARACTERS],
            'city' => ['bail', 'sometimes', 'nullable', 'string', 'max:' . DeliveryAddress::CITY_MAX_CHARACTERS],
            'state' => ['bail', 'sometimes', 'nullable', 'string', 'max:' . DeliveryAddress::STATE_MAX_CHARACTERS],
            'zip' => ['bail', 'sometimes', 'nullable', 'string', 'max:' . DeliveryAddress::ZIP_MAX_CHARACTERS],
            'country' => ['bail', 'required', 'string', 'size:2'],
            'place_id' => ['bail', 'sometimes', 'nullable', 'string'],
            'latitude' => ['bail', 'sometimes', 'nullable', 'numeric', 'min:-90', 'max:90'],
            'longitude' => ['bail', 'sometimes', 'nullable', 'numeric', 'min:-180', 'max:180'],
            'description' => ['bail', 'sometimes', 'nullable', 'string']
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
