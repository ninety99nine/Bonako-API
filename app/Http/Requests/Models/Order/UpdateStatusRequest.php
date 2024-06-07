<?php

namespace App\Http\Requests\Models\Order;

use App\Models\Order;
use Illuminate\Support\Str;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusRequest extends FormRequest
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
             *  Convert the "status" to the correct format if it has been set on the request inputs
             *
             *  Example: convert "onItsWay" or "On Its Way" into "on its way"
             */
            if($this->has('status')) {
                $this->merge([
                    'status' => $this->separateWordsThenLowercase($this->get('status'))
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
        //  Get the available order statuses
        $statuses = collect(Order::STATUSES)->map(fn($status) => strtolower($status));

        //  Get the order status to update this order
        $status = $this->separateWordsThenLowercase(request()->input('status'));

        /**
         *  If the order is being marked as completed, then we need to take
         *  extra precautions, so in that case we need to request the
         *  collection code.
         */
        $requiresConfirmation = $status == 'completed';

        return [
            'status' => ['required', 'string', Rule::in($statuses)],

            //  Collection code
            'collection_code' => array_merge(
                ['bail', 'sometimes', 'digits:6'],
                $requiresConfirmation ? ['required'] : []
            ),
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
            'status.string' => 'Answer "'.collect(Order::STATUSES)->join('", "', '" or "').' to update order status',
            'status.in' => 'Answer "'.collect(Order::STATUSES)->join('", "', '" or "').' to update order status',
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
