<?php

namespace App\Http\Requests\Models\AiLesson;

use App\Models\AiLesson;
use App\Traits\Base\BaseTrait;
use Illuminate\Foundation\Http\FormRequest;

class CreateAiLessonRequest extends FormRequest
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
            'name' => ['bail', 'required', 'string', 'min:'.AiLesson::NAME_MIN_CHARACTERS, 'max:'.AiLesson::NAME_MAX_CHARACTERS],
            'topics' => ['required', 'array'],
            'topics.*.title' => ['required', 'string'],
            'topics.*.prompt' => ['required', 'string'],
        ];
    }

    /**
     * Get the error lessons for the defined validation rules.
     *
     * @return array
     */
    public function lessons()
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
