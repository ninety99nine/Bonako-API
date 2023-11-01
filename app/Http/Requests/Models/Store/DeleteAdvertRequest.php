<?php

namespace App\Http\Requests\Models\Store;

use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;

class DeleteAdvertRequest extends FormRequest
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
            'position' => ['bail', 'required', 'integer', 'numeric', 'min:1', 'max:'.Store::MAXIMUM_ADVERTS],
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
            'advert.max' => 'The :attribute must not be greater than 2 megabytes',
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
