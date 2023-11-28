<?php

namespace App\Http\Requests\Models\FriendGroup;

use App\Models\FriendGroup;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowFriendGroupsRequest extends FormRequest
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
        /**
         *  Convert the "filter" to the correct format if it has been set on the request inputs
         *
         *  Example: convert "waiting" or "Waiting" into "waiting"
         */
        if($this->has('filter')) {
            $this->merge([
                'filter' => $this->separateWordsThenLowercase($this->get('filter'))
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
        $filters = collect(FriendGroup::FILTERS)->map(fn($filter) => strtolower($filter));

        return [
            'filter' => ['sometimes', 'string', Rule::in($filters)],
            'with_count_users' => ['bail', 'sometimes', 'required', 'boolean'],
            'with_count_stores' => ['bail', 'sometimes', 'required', 'boolean'],
            'with_count_orders' => ['bail', 'sometimes', 'required', 'boolean'],
            'with_count_friends' => ['bail', 'sometimes', 'required', 'boolean'],
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
            'filter.string' => 'Answer "'.collect(FriendGroup::FILTERS)->join('", "', '" or "').' to filter friend groups',
            'filter.in' => 'Answer "'.collect(FriendGroup::FILTERS)->join('", "', '" or "').' to filter friend groups',
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
