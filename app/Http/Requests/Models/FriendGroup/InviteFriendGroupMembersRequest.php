<?php

namespace App\Http\Requests\Models\FriendGroup;

use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Pivots\FriendGroupUserAssociation;

class InviteFriendGroupMembersRequest extends FormRequest
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
     *  We want to modify the request input before validating
     *
     *  Reference: https://laracasts.com/discuss/channels/requests/modify-request-input-value-before-validation
     */
    public function getValidatorInstance()
    {
        try {
            /**
             *  Convert the "role" to the correct format if it has been set on the request inputs
             *
             *  Example: convert "Admin" into "admin"
             */
            if($this->has('role')) {
                $this->merge([
                    'role' => $this->separateWordsThenLowercase($this->get('role'))
                ]);
            }

            //  Make sure that the "mobile_numbers" is an array if provided
            if($this->has('mobile_numbers') && is_string($this->request->all()['mobile_numbers'])) {
                $this->merge([
                    'mobile_numbers' => json_decode($this->request->all()['mobile_numbers'])
                ]);
            }

        } catch (\Throwable $th) {

        }

        return parent::getValidatorInstance();
    }

    /**
     *  Return the user friend group association roles
     *
     *  @return \Illuminate\Support\Collection
     */
    public function getRoles()
    {
        return collect(FriendGroupUserAssociation::ROLES)->reject((fn($role) => $role == 'Creator'))->map(fn($role) => $this->separateWordsThenLowercase($role));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'mobile_numbers' => ['required', 'array'],
            'mobile_numbers.*' => ['bail', 'distinct', 'phone'],
            'role' => ['bail', 'sometimes', 'string', Rule::in($this->getRoles())],
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
            'role.in' => 'Answer "'.collect($this->getRoles())->join('", "', '" or "').' to indicate the role',
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
