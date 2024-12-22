<?php

namespace App\Http\Requests\Models\Address;

use App\Models\Address;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;

class CreateAddressRequest extends FormRequest
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
            'type' => ['bail', 'sometimes', 'nullable', Rule::in(Address::TYPES())],
            'address_line' => ['bail', 'required', 'string', 'max:' . Address::ADDRESS_MAX_CHARACTERS],
            'address_line2' => ['bail', 'sometimes', 'nullable', 'string', 'max:' . Address::ADDRESS2_MAX_CHARACTERS],
            'city' => ['bail', 'sometimes', 'nullable', 'string', 'max:' . Address::CITY_MAX_CHARACTERS],
            'state' => ['bail', 'sometimes', 'nullable', 'string', 'max:' . Address::STATE_MAX_CHARACTERS],
            'postal_code' => ['bail', 'sometimes', 'nullable', 'string', 'max:' . Address::POSTAL_CODE_MAX_CHARACTERS],
            'country' => ['bail', 'required', 'string', 'size:2'],
            'place_id' => ['bail', 'sometimes', 'nullable', 'string'],
            'latitude' => ['bail', 'sometimes', 'nullable', 'numeric', 'min:-90', 'max:90'],
            'longitude' => ['bail', 'sometimes', 'nullable', 'numeric', 'min:-180', 'max:180'],
            'description' => ['bail', 'sometimes', 'nullable', 'string'],
            'customer_id' => ['required', 'uuid'],
            'user_id' => ['bail', 'required_without_all:store_id,customer_id,delivery_method_id', 'uuid'],
            'store_id' => ['bail', 'required_without_all:user_id,customer_id,delivery_method_id', 'uuid'],
            'customer_id' => ['bail', 'required_without_all:user_id,store_id,delivery_method_id', 'uuid'],
            'delivery_method_id' => ['bail', 'required_without_all:user_id,store_id,customer_id', 'uuid'],
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
