<?php

namespace App\Http\Requests\Models\AiMessage;

use App\Models\AiMessage;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CreateAiMessageRequest extends FormRequest
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
            'user_content' => ['bail', 'required', 'string', 'min:'.AiMessage::USER_CONTENT_MIN_CHARACTERS, 'max:'.AiMessage::USER_CONTENT_MAX_CHARACTERS],
            'category_id' => ['bail', 'required', 'numeric', 'min:1', 'exists:ai_message_categories,id']
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
