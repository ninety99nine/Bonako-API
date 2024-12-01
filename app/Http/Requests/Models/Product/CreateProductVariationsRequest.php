<?php

namespace App\Http\Requests\Models\Product;

use App\Models\Variable;
use Illuminate\Foundation\Http\FormRequest;

class CreateProductVariationsRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //  Array
            'variant_attributes' => ['required', 'array'],

            //  Array "name" key
            'variant_attributes.*.name' => ['bail', 'required', 'string', 'min:'.Variable::NAME_MIN_CHARACTERS, 'max:'.Variable::NAME_MAX_CHARACTERS, 'distinct'],

            //  Array "name" key
            'variant_attributes.*.instruction' => ['bail', 'sometimes', 'string', 'min:'.Variable::INSTRUCTION_MIN_CHARACTERS, 'max:'.Variable::INSTRUCTION_MAX_CHARACTERS],

            //  Array "values" key
            'variant_attributes.*.values' => ['required', 'array', 'min:1'],
            'variant_attributes.*.values.*' => ['bail', 'required', 'string', 'min:'.Variable::VALUE_MIN_CHARACTERS, 'max:'.Variable::VALUE_MAX_CHARACTERS, 'distinct']
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
        return [
            'variant_attributes.*.name' => 'variant attribute name',
            'variant_attributes.*.values.*' => 'variant attribute value'
        ];
    }
}
