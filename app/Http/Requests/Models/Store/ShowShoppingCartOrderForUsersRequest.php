<?php

namespace App\Http\Requests\Models\Store;

use App\Models\Order;
use Illuminate\Support\Str;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowShoppingCartOrderForUsersRequest extends FormRequest
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
             *  Convert the "order_for" to the correct format if it has been set on the request inputs
             *
             *  Example: convert "Me And Friends" or "me and Friends" into "me and friends"
             */
            if($this->has('order_for')) {
                $this->merge([
                    'order_for' => $this->separateWordsThenLowercase($this->get('order_for'))
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
        $orderForOptions = collect(Order::ORDER_FOR_OPTIONS)->map(fn($filter) => strtolower($filter));

        return [
            'order_for' => ['required', 'string', Rule::in($orderForOptions)],

            'friend_group_ids' => ['sometimes', 'array'],
            'friend_group_ids.*' => ['bail', 'required', 'integer', 'numeric', 'min:1', 'distinct'],

            'friend_user_ids' => ['sometimes', 'array'],
            'friend_user_ids.*' => ['bail', 'required', 'integer', 'numeric', 'min:1', 'distinct']
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
            'filter.string' => 'Answer "'.collect(Order::ORDER_FOR_OPTIONS)->join('", "', '" or "').' to indicate who the order is for',
            'filter.in' => 'Answer "'.collect(Order::ORDER_FOR_OPTIONS)->join('", "', '" or "').' to indicate who the order is for',
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
