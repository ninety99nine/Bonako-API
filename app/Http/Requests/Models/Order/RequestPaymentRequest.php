<?php

namespace App\Http\Requests\Models\Order;

use App\Models\PaymentMethod;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class RequestPaymentRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'embed' => ['bail', 'sometimes', 'required', 'boolean'],
            'percentage' => ['bail', 'required_without:amount', 'numeric', 'min:1', 'max:100'],
            'payment_method_id' => ['bail', 'required', 'numeric', 'min:1',
                Rule::exists('payment_methods', 'id')->whereIn('method', ['Orange Money', 'DPO Card']),
            ],
            'amount' => ['bail', 'required_without:percentage', 'min:0', 'not_in:0', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'mobile_number' => ['bail', 'sometimes', 'required', 'string', 'starts_with:267', 'regex:/^[0-9]+$/', 'size:11', 'exists:users,mobile_number'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        $mobileNumberWithoutExtension = $this->convertToMobileNumberFormat(request()->input('mobile_number'))->withoutExtension;

        return [
            'mobile_number.regex' => 'The mobile number must only contain numbers',
            'mobile_number.exists' => 'The account using the mobile number '.$mobileNumberWithoutExtension.' does not exist.',
            'payment_method_id.exists' => 'Answer "'.collect(PaymentMethod::whereIn('method', ['Orange Money', 'DPO Card'])->orderBy('position', 'asc')->pluck('id'))->join('", "', '" or "').' to indicate the payment method',
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
