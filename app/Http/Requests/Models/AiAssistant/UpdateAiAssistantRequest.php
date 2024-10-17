<?php

namespace App\Http\Requests\Models\AiAssistant;

use App\Traits\Base\BaseTrait;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAiAssistantRequest extends FormRequest
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
            'remaining_free_tokens' => ['bail', 'sometimes', 'required', 'integer', 'min:0', 'max:10000'],
            'remaining_paid_tokens' => ['bail', 'sometimes', 'required', 'integer', 'min:0', 'max:10000'],
            'remaining_paid_top_up_tokens' => ['bail', 'sometimes', 'required', 'integer', 'min:0', 'max:10000'],
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
