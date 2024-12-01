<?php

namespace App\Http\Requests\Models\Occasion;

use App\Models\Occasion;
use App\Traits\Base\BaseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOccasionRequest extends FormRequest
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
                'bail', 'sometimes', 'string', 'min:'.Occasion::NAME_MIN_CHARACTERS, 'max:'.Occasion::NAME_MAX_CHARACTERS,
                Rule::unique('occasions')->ignore(request()->occasionId)
            ],
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
