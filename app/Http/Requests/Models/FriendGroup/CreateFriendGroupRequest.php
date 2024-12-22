<?php

namespace App\Http\Requests\Models\FriendGroup;

use App\Models\FriendGroup;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Pivots\FriendGroupUserAssociation;

class CreateFriendGroupRequest extends FormRequest
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
        $userId = request()->current_user->id;

        return [
            'name' => [
                'bail', 'required', 'string', 'min:'.FriendGroup::NAME_MIN_CHARACTERS, 'max:'.FriendGroup::NAME_MAX_CHARACTERS,
                /**
                 *  Make sure that this friend group name does not
                 *  already exist for the same user
                 */
                function ($attribute, $value, $fail) use ($userId) {
                    $friendGroupExists = FriendGroup::where('name', $value)
                        ->whereHas('users', function ($query) use ($userId) {
                            $query->where('users.id', $userId);
                        })->exists();

                    if ($friendGroupExists) {
                        $fail('This name already exists');
                    }
                },
            ],
            'mobile_numbers' => ['array'],
            'mobile_numbers.*' => ['bail', 'distinct', 'string', 'phone'],
            'emoji' => ['bail', 'sometimes', 'nullable', 'string'],
            'shared' => ['bail', 'sometimes', 'boolean'],
            'can_add_friends' => ['bail', 'sometimes', 'boolean'],
            'role' => ['bail', 'sometimes', 'string', Rule::in($this->getRoles())],
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
