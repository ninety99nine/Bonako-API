<?php

namespace App\Http\Requests\Models\PaymentMethod;

use App\Models\PaymentMethod;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use App\Services\Country\CountryService;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentMethodRequest extends FormRequest
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
            'return' => ['sometimes', 'boolean'],
            'active' => ['sometimes', 'required', 'boolean'],
            'name' => [
                'bail', 'sometimes', 'required', 'string', 'min:'.PaymentMethod::NAME_MIN_CHARACTERS, 'max:'.PaymentMethod::NAME_MAX_CHARACTERS,
                Rule::unique('payment_methods')->ignore(request()->paymentMethodId)
            ],
            'type' => [
                'bail', 'sometimes', 'required', 'string', 'min:'.PaymentMethod::TYPE_MIN_CHARACTERS, 'max:'.PaymentMethod::TYPE_MAX_CHARACTERS,
                Rule::unique('payment_methods')->ignore(request()->paymentMethodId)
            ],
            'description' => ['bail', 'sometimes', 'nullable', 'required', 'string', 'min:'.PaymentMethod::DESCRIPTION_MIN_CHARACTERS, 'max:'.PaymentMethod::DESCRIPTION_MAX_CHARACTERS],
            'category' => ['bail', 'sometimes', 'required', Rule::in(PaymentMethod::PAYMENT_METHOD_CATEGORIES())],
            'countries' => ['sometimes', 'nullable', 'array', 'required'],
            'countries.*' => ['string', Rule::in(collect((new CountryService)->getCountries())->map(fn($country) => $country->iso)->toArray())],
            'metadata' => ['sometimes', 'nullable', 'array', 'required'],
            'can_pay_later' => ['sometimes', 'required', 'boolean'],
            'require_proof_of_payment' => ['sometimes', 'required', 'boolean'],
            'automatically_mark_as_paid' => ['sometimes', 'required', 'boolean'],
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
