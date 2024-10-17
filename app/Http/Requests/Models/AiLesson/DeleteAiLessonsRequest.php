<?php

namespace App\Http\Requests\Models\AiLesson;

use Illuminate\Foundation\Http\FormRequest;

class DeleteAiLessonsRequest extends FormRequest
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
            'ai_lesson_ids' => ['required', 'array'],
            'ai_lesson_ids.*' => ['bail', 'required', 'uuid', 'distinct']
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
