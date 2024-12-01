<?php

namespace App\Http\Requests\Models\Product;

use App\Models\Variable;
use Illuminate\Foundation\Http\FormRequest;

class ShowVariationsRequest extends FormRequest
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
            'variant_attribute_choices.*' => ['sometimes', 'string'],
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
