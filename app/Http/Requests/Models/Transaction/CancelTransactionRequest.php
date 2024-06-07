<?php

namespace App\Http\Requests\Models\Transaction;

use App\Models\Transaction;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CancelTransactionRequest extends FormRequest
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
             *  Example: convert "Refund" into "refund"
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
        $cancellationReasons = collect(Transaction::CANCELLATION_REASONS)->map(fn($cancellationReason) => strtolower($cancellationReason));

        return [
            'cancellation_reason' => ['sometimes', 'bail', 'string', Rule::in($cancellationReasons)],
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
            'cancellation_reason.in' => 'Answer "'.collect(Transaction::CANCELLATION_REASONS)->join('", "', '" or "').'" for the cancellation reason',
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
