<?php

namespace App\Http\Requests\Models\Order;

use App\Models\Order;
use Illuminate\Support\Str;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowOrdersRequest extends FormRequest
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
        $filters = collect(Order::STORE_ORDER_FILTERS)->map(fn($filter) => strtolower($filter));

        return [
            'start_at_order_id' => ['sometimes', 'required', 'integer', 'numeric', 'min:1'],
            'customer_user_id' => ['sometimes', 'required', 'integer', 'numeric', 'min:1'],
            'friend_user_id' => ['sometimes', 'required', 'integer', 'numeric', 'min:1'],
            'except_order_id' => ['sometimes', 'required', 'integer', 'numeric', 'min:1'],
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
