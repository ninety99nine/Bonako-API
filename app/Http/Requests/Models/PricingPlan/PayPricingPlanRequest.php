<?php

namespace App\Http\Requests\Models\PricingPlan;

use App\Models\PaymentMethod;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class PayPricingPlanRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'return' => ['sometimes', 'boolean'],
            'store_id' => ['bail', 'sometimes', 'uuid'],
            'payment_method_id' => ['bail', 'required_without:payment_method_type', 'uuid'],
            'payment_method_type' => ['bail', 'required_without:payment_method_id', Rule::in(PaymentMethod::PAYMENT_METHOD_TYPES())]
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [];
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
