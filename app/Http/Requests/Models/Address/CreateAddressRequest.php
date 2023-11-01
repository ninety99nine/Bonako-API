<?php

namespace App\Http\Requests\Models\Address;

use App\Models\Address;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $userId = $this->chooseUser()->id;

        return [
            'name' => [
                'bail', 'required', 'string', 'min:'.Address::NAME_MIN_CHARACTERS, 'max:'.Address::NAME_MAX_CHARACTERS,
                /**
                 *  Make sure that this address name does not
                 *  already exist for the same user
                 */
                Rule::unique('addresses')->where('user_id', $userId)
            ],
            'share_address' => ['bail', 'sometimes', 'required', 'boolean'],
            'address_line' => ['bail', 'required', 'string', 'min:'.Address::ADDRESS_LINE_MIN_CHARACTERS, 'max:'.Address::ADDRESS_LINE_MAX_CHARACTERS]
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
            'unique' => 'This name already exists'
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
