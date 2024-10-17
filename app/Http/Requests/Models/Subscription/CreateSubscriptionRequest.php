<?php

namespace App\Http\Requests\Models\Subscription;

use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CreateSubscriptionRequest extends FormRequest
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
            'duration' => ['bail', 'required', 'integer', 'min:1'],
            'store_id' => ['bail', 'required_without:ai_assistant_id', 'uuid'],
            'ai_assistant_id' => ['bail', 'required_without:store_id', 'uuid'],
            'credits' => ['bail', 'required_with:ai_assistant_id', 'integer', 'min:1'],
            'frequency' => ['bail', 'required', Rule::in(['day', 'week', 'month', 'year'])],
            'replace_credits' => ['bail', 'sometimes', 'required_with:ai_assistant_id', 'boolean'],
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
