<?php

namespace App\Http\Requests\Models\FriendGroup;

use App\Models\FriendGroup;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFriendGroupRequest extends FormRequest
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
        $userId = request()->current_user->id;
        $friendGroupId = request()->friendGroupId;

        return [
            'name' => [
                'bail', 'sometimes', 'string', 'min:'.FriendGroup::NAME_MIN_CHARACTERS, 'max:'.FriendGroup::NAME_MAX_CHARACTERS,
                /**
                 *  Make sure that this friend group name does not
                 *  already exist for the same user
                 */
                function ($attribute, $value, $fail) use ($friendGroupId, $userId) {
                    $friendGroupExists = FriendGroup::where('name', $value)
                        ->where('friend_groups.id', '!=', $friendGroupId)
                        ->whereHas('users', function ($query) use ($userId) {
                            $query->where('users.id', $userId);
                        })->exists();

                    if ($friendGroupExists) {
                        $fail('This name already exists');
                    }
                },
            ],
            'mobile_numbers' => ['array'],
            'emoji' => ['bail', 'sometimes', 'nullable', 'string'],
            'shared' => ['bail', 'sometimes', 'boolean'],
            'mobile_numbers.*' => ['bail', 'sometimes', 'distinct', 'phone'],
            'can_add_friends' => ['bail', 'sometimes', 'boolean'],
            'description' => ['bail', 'sometimes', 'nullable', 'string', 'min:'.FriendGroup::DESCRIPTION_MIN_CHARACTERS, 'max:'.FriendGroup::DESCRIPTION_MAX_CHARACTERS],
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
        return [
            'mobile_numbers.*' => 'mobile number'
        ];
    }
}
