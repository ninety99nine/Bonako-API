<?php

namespace App\Http\Requests\Models\User;

use Illuminate\Foundation\Http\FormRequest;

class CreateFriendRequest extends FormRequest
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
        //  If the request provided a user e.g /users/{user}
        if(request()->user) {

            //  Get this request user
            $user = request()->user;

        //  If the request did not provide a user
        }else{

            /**
             *  Get this authenticated user
             *
             *  @var User $user
             */
            $user = request()->auth_user;

        }

        $mobileNumber = $user->mobile_number->withExtension;

        return [
            'mobile_numbers' => ['required', 'array'],
            'mobile_numbers.*' => ['bail', 'string', 'distinct', 'starts_with:267', 'regex:/^[0-9]+$/', 'size:11', 'not_in:'.$mobileNumber, 'exists:users,mobile_number'],
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
            'mobile_numbers.*.not_in' => 'You cannot add your own mobile number',
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
