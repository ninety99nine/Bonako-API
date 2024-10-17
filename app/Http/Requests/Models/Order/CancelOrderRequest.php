<?php

namespace App\Http\Requests\Models\Order;

use App\Models\Order;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CancelOrderRequest extends FormRequest
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
             *  Convert the "cancellation_reason" to the correct format if it has been set on the request inputs
             *
             *  Example: convert "no stock" into "No Stock"
             */
            if($this->has('cancellation_reason')) {
                $this->merge([
                    'cancellation_reason' => $this->separateWordsThenLowercase($this->get('cancellation_reason'))
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
        $cancellationReasons = collect(Order::CANCELLATION_REASONS())->map(fn($cancellationReason) => $this->separateWordsThenLowercase($cancellationReason));

        return [
            'cancellation_reason' => ['sometimes', 'bail', 'nullable', Rule::in($cancellationReasons)],
            'other_cancellation_reason' => ['sometimes', 'bail', 'nullable', 'string', 'min:'.Order::OTHER_CANCELLATION_REASON_MIN_CHARACTERS, 'max:'.Order::OTHER_CANCELLATION_REASON_MAX_CHARACTERS],
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
            'cancellation_reason.in' => 'Answer "'.collect(Order::CANCELLATION_REASONS())->join('", "', '" or "').'" for the cancellation reason',
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
