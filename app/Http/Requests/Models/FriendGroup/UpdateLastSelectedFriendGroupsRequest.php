<?php

namespace App\Http\Requests\Models\FriendGroup;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLastSelectedFriendGroupsRequest extends FormRequest
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
            'friend_group_ids' => ['required', 'array'],
            'friend_group_ids.*' => ['bail', 'required', 'uuid', 'distinct']
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
