<?php

namespace App\Http\Requests\Models\Store;

use Illuminate\Support\Str;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Pivots\UserStoreAssociation;

class ShowTeamMembersRequest extends FormRequest
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
        /**
         *  Convert the "filter" to the correct format if it has been set on the request inputs
         *
         *  Example: convert "joined" or "Joined" into "joined"
         */
        if($this->request->has('filter')) {
            $this->merge([
                'filter' => $this->separateWordsThenLowercase($this->request->get('filter'))
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
        $filters = collect(UserStoreAssociation::TEAM_MEMBER_FILTERS)->map(fn($filter) => strtolower($filter));

        return [
            'filter' => ['sometimes', 'string', Rule::in($filters)],
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
            'filter.string' => 'Answer "'.collect(UserStoreAssociation::TEAM_MEMBER_FILTERS)->join('", "', '" or "').' to filter team members',
            'filter.in' => 'Answer "'.collect(UserStoreAssociation::TEAM_MEMBER_FILTERS)->join('", "', '" or "').' to filter team members',
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
