<?php

namespace App\Http\Requests\Models\Store;

use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAdvertRequest extends FormRequest
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
            'advert' => ['bail', 'required', 'mimetypes:image/jpeg,image/png,image/jpg,image/gif,image/bmp', 'max:4096'],
            'position' => ['bail', 'required', 'integer', 'min:1', 'max:'.Store::MAXIMUM_ADVERTS],
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
            'advert.max' => 'The :attribute must not be greater than 4 megabytes',
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
