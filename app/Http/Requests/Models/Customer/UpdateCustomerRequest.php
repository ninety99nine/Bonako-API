<?php

namespace App\Http\Requests\Models\Customer;

use App\Models\Customer;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
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
        $storeId = request()->storeId ?? request()->input('store_id');

        return [
            'first_name' => ['bail', 'sometimes', 'string', 'min:'.Customer::FIRST_NAME_MIN_CHARACTERS, 'max:'.Customer::FIRST_NAME_MAX_CHARACTERS],
            'last_name' => ['bail', 'nullable', 'string', 'min:'.Customer::LAST_NAME_MIN_CHARACTERS, 'max:'.Customer::LAST_NAME_MAX_CHARACTERS],
            'notes' => ['bail', 'nullable', 'string', 'min:'.Customer::NOTES_MIN_CHARACTERS, 'max:'.Customer::NOTES_MAX_CHARACTERS],
            'mobile_number' => [
                'bail', 'nullable', 'string', 'phone',
                //  Make sure that this customer mobile number does not already exist for the same store
                Rule::unique('customers')->where('store_id', $storeId)->ignore(request()->customerId)
            ],
            'email' => [
                'bail', 'nullable', 'string', 'email',
                //  Make sure that this customer email does not already exist for the same store
                Rule::unique('customers')->where('store_id', $storeId)->ignore(request()->customerId)
            ],
            'birthday' => ['bail', 'nullable', 'date', 'before:today'],
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
