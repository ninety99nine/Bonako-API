<?php

namespace App\Http\Requests\Models\FriendGroup;

use App\Enums\Association;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowFriendGroupsRequest extends FormRequest
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
            'association' => ['bail', 'sometimes', 'nullable', Rule::in(
                Association::SUPER_ADMIN->value
            )]
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
