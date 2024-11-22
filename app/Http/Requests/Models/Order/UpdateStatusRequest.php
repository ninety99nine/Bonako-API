<?php

namespace App\Http\Requests\Models\Order;

use App\Models\Order;
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
                    'status' => $this->separateWordsThenLowercase($this->get('status')),
                ]);
            }

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
        $status = $this->separateWordsThenLowercase(request()->input('status'));
        $statuses = collect(Order::STATUSES())->map(fn($status) => strtolower($status));
        $cancellationReasons = collect(Order::CANCELLATION_REASONS())->map(fn($cancellationReason) => $this->separateWordsThenLowercase($cancellationReason));

        return [
            'status' => ['required', 'string', Rule::in($statuses)],
            'collection_code' => ['bail', 'sometimes', 'digits:6'],
            'cancellation_reason' => ['sometimes', 'bail', 'nullable', Rule::in($cancellationReasons)],
            'collection_note' => ['bail', 'sometimes', 'string', 'min:'.Order::COLLECTION_NOTE_MIN_CHARACTERS, 'max:'.Order::COLLECTION_NOTE_MAX_CHARACTERS],
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
            'status.in' => 'Answer "'.collect(Order::STATUSES())->join('", "', '" or "').' to update order status',
            'status.string' => 'Answer "'.collect(Order::STATUSES())->join('", "', '" or "').' to update order status',
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
