<?php

namespace App\Http\Requests\Models\Order;

use App\Models\PaymentMethod;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class MarkAsPaidRequest extends FormRequest
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

            if($this->has('payment_method_type')) {
                $this->merge(['payment_method_type' => $this->separateWordsThenLowercase($this->get('payment_method_type'))]);
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
        return [
            'payment_method_id' => ['bail', 'required_without:payment_method_type', 'uuid'],
            'percentage' => ['bail', 'required_without:amount', 'numeric', 'min:1', 'max:100'],
            'amount' => ['bail', 'required_without:percentage', 'min:0', 'not_in:0', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'payment_method_type' => ['required', 'required_without:payment_method_id', Rule::in(PaymentMethod::PAYMENT_METHOD_TYPES())],
            'transaction_proof_of_payment_photo' => ['bail', 'nullable', 'mimetypes:image/jpeg,image/png,image/jpg,image/gif,image/bmp', 'max:4096'],
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
            'transaction_proof_of_payment_photo.max' => 'The :attribute must not be greater than 4 megabytes'
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
