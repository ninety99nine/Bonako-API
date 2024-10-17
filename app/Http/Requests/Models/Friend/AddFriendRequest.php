<?php

namespace App\Http\Requests\Models\Friend;

use App\Models\Friend;
use Illuminate\Foundation\Http\FormRequest;

class AddFriendRequest extends FormRequest
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

        return [
            'first_name' => ['bail', 'required', 'string', 'min:'.Friend::FIRST_NAME_MIN_CHARACTERS, 'max:'.Friend::FIRST_NAME_MAX_CHARACTERS],
            'last_name' => ['bail', 'required', 'string', 'min:'.Friend::LAST_NAME_MIN_CHARACTERS, 'max:'.Friend::LAST_NAME_MAX_CHARACTERS],
            'mobile_number' => [
                'bail', 'required', 'phone',
                function ($attribute, $value, $fail) use ($userId) {

                    $friendExists = Friend::where('user_id', $userId)
                        ->where('mobile_number', $value)
                        ->exists();

                    if ($friendExists) {
                        $fail('A friend using this mobile number already exists');
                    }

                },
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
}
