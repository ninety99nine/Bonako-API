<?php

namespace App\Http\Requests\Models\Store;

use Illuminate\Foundation\Http\FormRequest;

class AddStoreToFriendGroupsRequest extends FormRequest
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
     *  We want to modify the request input before validating
     *
     *  Reference: https://laracasts.com/discuss/channels/requests/modify-request-input-value-before-validation
     */
    public function getValidatorInstance()
    {
        //  Make sure that the "friend_group_ids" is an array if provided
        if($this->request->has('friend_group_ids') && is_string($this->request->get('friend_group_ids'))) {
            $this->merge([
                'friend_group_ids' => json_decode($this->request->get('friend_group_ids'))
            ]);
        }

        return parent::getValidatorInstance();
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
            'friend_group_ids.*' => ['bail', 'required', 'integer', 'numeric', 'min:1', 'distinct']
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