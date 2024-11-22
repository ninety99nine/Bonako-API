<?php

namespace App\Http\Requests\Models\Order;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class VerifyOrderCollectionRequest extends FormRequest
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
            'collection_code' => ['bail', 'required', 'digits:6'],
            'collection_note' => ['bail', 'sometimes', 'string', 'min:'.Order::COLLECTION_NOTE_MIN_CHARACTERS, 'max:'.Order::COLLECTION_NOTE_MAX_CHARACTERS],
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
