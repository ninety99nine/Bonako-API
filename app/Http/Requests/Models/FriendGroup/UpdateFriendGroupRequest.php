<?php

namespace App\Http\Requests\Models\FriendGroup;

use App\Models\FriendGroup;
use App\Traits\Base\BaseTrait;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFriendGroupRequest extends FormRequest
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
        //  Get the friend group id
        $friendGroupId = request()->friend_group->id;

        //  Get the user's id
        $userId = $this->chooseUser()->id;

        return [
            'name' => [
                'bail', 'sometimes', 'required', 'string', 'min:'.FriendGroup::NAME_MIN_CHARACTERS, 'max:'.FriendGroup::NAME_MAX_CHARACTERS,
                /**
                 *  Make sure that this friend group name does not
                 *  already exist for the same user
                 */
                function ($attribute, $value, $fail) use ($friendGroupId, $userId) {
                    $friendGroupExists = FriendGroup::where('name', $value)
                        ->where('id', '!=', $friendGroupId)
                        ->whereHas('users', function ($query) use ($userId) {
                            $query->where('user_id', $userId);
                        })->exists();

                    if ($friendGroupExists) {
                        $fail('This name already exists');
                    }
                },
            ],
            'mobile_numbers' => ['array'],
            'emoji' => ['bail', 'sometimes', 'nullable', 'string'],
            'shared' => ['bail', 'sometimes', 'required', 'boolean'],
            'can_add_friends' => ['bail', 'sometimes', 'required', 'boolean'],
            'mobile_numbers.*' => ['bail', 'sometimes', 'string', 'distinct', 'starts_with:267', 'regex:/^[0-9]+$/', 'size:11'],
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
        return [
            'mobile_numbers.*.regex' => 'The mobile number must only contain numbers',
            'mobile_numbers.*.exists' => 'The account using this mobile number does not exist',
        ];
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
