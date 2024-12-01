<?php

namespace App\Http\Requests\Models\FriendGroup;

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
        try {
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
        $userOrderAssociations = collect(Order::USER_ORDER_ASSOCIATIONS)->map(fn($userOrderAssociation) => $this->separateWordsThenLowercase($userOrderAssociation));
        $filters = collect(Order::STORE_ORDER_FILTERS)->map(fn($filter) => strtolower($filter));

        return [
            'user_order_association' => ['required', 'string', Rule::in($userOrderAssociations)],
            'start_at_order_id' => ['sometimes', 'uuid'],
            'store_id' => ['bail', 'sometimes', 'uuid'],
            'with_customer' => ['bail', 'sometimes', 'boolean'],
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
            'user_order_association.string' => 'Answer "'.collect(Order::USER_ORDER_ASSOCIATIONS)->join('", "', '" or "').' for the user order association',
            'user_order_association.in' => 'Answer "'.collect(Order::USER_ORDER_ASSOCIATIONS)->join('", "', '" or "').' for the user order association',
            'filter.string' => 'Answer "'.collect(Order::STORE_ORDER_FILTERS)->join('", "', '" or "').' to filter orders',
            'filter.in' => 'Answer "'.collect(Order::STORE_ORDER_FILTERS)->join('", "', '" or "').' to filter orders',
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
