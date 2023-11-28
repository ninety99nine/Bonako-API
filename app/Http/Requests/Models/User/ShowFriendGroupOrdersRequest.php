<?php

namespace App\Http\Requests\Models\User;

use App\Models\Order;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowFriendGroupOrdersRequest extends FormRequest
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
         *  Example: convert "waiting" or "Waiting" into "waiting"
         */
        if($this->has('filter')) {
            $this->merge([
                'filter' => $this->separateWordsThenLowercase($this->get('filter'))
            ]);
        }
        /**
         *  Convert the "user_order_association" to the correct format if it has been set on the request inputs
         *
         *  Example: convert "teamMember" or "Team Member" into "team member"
         */
        if($this->has('user_order_association')) {
            $this->merge([
                'user_order_association' => $this->separateWordsThenLowercase($this->get('user_order_association'))
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
        $filters = collect(Order::FRIEND_GROUP_ORDER_FILTERS)->map(fn($filter) => strtolower($filter));
        $userOrderAssociations = collect(Order::USER_ORDER_ASSOCIATIONS)->map(fn($userOrderAssociation) => $this->separateWordsThenLowercase($userOrderAssociation));

        return [
            'user_order_association' => ['required', 'string', Rule::in($userOrderAssociations)],
            'start_at_order_id' => ['sometimes', 'required', 'integer', 'numeric', 'min:1'],
            'store_id' => ['sometimes', 'required', 'integer', 'numeric', 'min:1'],
            'with_customer' => ['bail', 'sometimes', 'required', 'boolean'],
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
            'filter.in' => 'Answer "'.collect(Order::FRIEND_GROUP_ORDER_FILTERS)->join('", "', '" or "').' to filter orders',
            'filter.string' => 'Answer "'.collect(Order::FRIEND_GROUP_ORDER_FILTERS)->join('", "', '" or "').' to filter orders',
            'user_order_association.in' => 'Answer "'.collect(Order::USER_ORDER_ASSOCIATIONS)->join('", "', '" or "').' for user order association',
            'user_order_association.string' => 'Answer "'.collect(Order::USER_ORDER_ASSOCIATIONS)->join('", "', '" or "').' for user order association',
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
