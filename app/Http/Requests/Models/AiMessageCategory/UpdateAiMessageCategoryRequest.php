<?php

namespace App\Http\Requests\Models\AiMessageCategory;

use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use App\Models\AiMessageCategory;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAiMessageCategoryRequest extends FormRequest
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
            'name' => [
                'bail', 'sometimes', 'required', 'string', 'min:'.AiMessageCategory::NAME_MIN_CHARACTERS, 'max:'.AiMessageCategory::NAME_MAX_CHARACTERS,
                Rule::unique('occasions')->ignore(request()->aiMessageCategoryId)
            ],
            'description' => ['bail', 'sometimes', 'required', 'string', 'min:'.AiMessageCategory::DESCRIPTION_MIN_CHARACTERS, 'max:'.AiMessageCategory::DESCRIPTION_MAX_CHARACTERS],
            'system_prompt' => ['bail', 'sometimes', 'required', 'string', 'min:'.AiMessageCategory::SYSTEM_PROMPT_MIN_CHARACTERS, 'max:'.AiMessageCategory::SYSTEM_PROMPT_MAX_CHARACTERS],
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
