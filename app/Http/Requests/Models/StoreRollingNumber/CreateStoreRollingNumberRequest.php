<?php

namespace App\Http\Requests\Models\StoreRollingNumber;

use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CreateStoreRollingNumberRequest extends FormRequest
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
            'store_id' => ['required', 'uuid'],
            'mobile_number' => [
                'bail', 'required', 'phone',
                /**
                 *  Make sure that this product name does not already exist on the same store
                 *  (Except for the same product)
                 */
                Rule::unique('store_rolling_numbers')->where('store_id', request()->input('store_id'))
            ],
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
            'mobile_number.unique' => 'The mobile number has already been added'
        ];
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
