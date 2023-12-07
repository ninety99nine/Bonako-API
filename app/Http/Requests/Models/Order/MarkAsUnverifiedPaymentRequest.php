<?php

namespace App\Http\Requests\Models\Order;

use App\Traits\Base\BaseTrait;
use Illuminate\Foundation\Http\FormRequest;

class MarkAsUnverifiedPaymentRequest extends FormRequest
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
            'percentage' => ['bail', 'required_without:amount', 'numeric', 'min:1', 'max:100'],
            'payment_method_id' => ['bail', 'sometimes', 'required', 'numeric', 'min:1', 'exists:payment_methods,id'],
            'amount' => ['bail', 'required_without:percentage', 'min:0', 'not_in:0', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'proof_of_payment_photo' => ['bail', 'nullable', 'mimetypes:image/jpeg,image/png,image/jpg,image/gif,image/bmp', 'max:4096'],
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
            'proof_of_payment_photo.max' => 'The :attribute must not be greater than 4 megabytes',
            'mobile_number.exists' => 'The account using the mobile number '.$mobileNumberWithoutExtension.' does not exist.',
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
