<?php

namespace App\Http\Requests\Models\Store;

use App\Models\Store;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamMemberPermissionsRequest extends FormRequest
{
    use BaseTrait;

    private $permissions;

    public function __construct()
    {
        $this->permissions = collect(Store::PERMISSIONS)->map(fn($permission) => $permission['grant'])->toArray();
    }

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

            //  Make sure that the "permissions" is an array if provided
            if($this->has('permissions')) {

                if(is_string($this->get('permissions'))) {

                    $this->merge([
                        'permissions' => collect(json_decode($this->get('permissions')))->map(fn($permission) => $this->separateWordsThenLowercase($permission))->toArray()
                    ]);

                }else{

                    $this->merge([
                        'permissions' => collect($this->get('permissions'))->map(fn($permission) => $this->separateWordsThenLowercase($permission))->toArray()
                    ]);

                }
            }

            return parent::getValidatorInstance();

        } catch (\Throwable $th) {

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
        $permissions = collect($this->permissions)->map(fn($permission) => strtolower($permission));

        return [
            'permissions' => ['required', 'array'],
            'permissions.*' => ['bail', 'string',  Rule::in($permissions)]
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
            'permissions.*.string' => 'The following permissions are allowed: '.collect($this->permissions)->join(', ', ' or '),
            'permissions.*.in' => 'The following permissions are allowed: '.collect($this->permissions)->join(', ', ' or ')
        ];
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
