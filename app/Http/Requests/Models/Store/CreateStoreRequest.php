<?php

namespace App\Http\Requests\Models\Store;

use App\Models\Store;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use App\Services\Country\CountryService;
use App\Services\Currency\CurrencyService;
use App\Services\Language\LanguageService;
use Illuminate\Foundation\Http\FormRequest;

class CreateStoreRequest extends FormRequest
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
             *  Convert the "call_to_action" to the correct format if it has been set on the request inputs
             *
             *  Example: convert "buy" or "buyNow" into "buy now"
             */
            if($this->has('call_to_action')) {
                $this->merge([
                    'call_to_action' => $this->separateWordsThenLowercase($this->get('call_to_action'))
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
        return [
            'emoji' => ['bail', 'sometimes', 'nullable', 'string'],
            'logo' => ['bail', 'sometimes', 'nullable', 'mimetypes:image/jpeg,image/png,image/jpg,image/gif,image/bmp', 'max:4096'],
            'cover_photo' => ['bail', 'sometimes', 'nullable', 'mimetypes:image/jpeg,image/png,image/jpg,image/gif,image/bmp', 'max:4096'],
            'name' => ['bail', 'required', 'string', 'min:'.Store::NAME_MIN_CHARACTERS, 'max:'.Store::NAME_MAX_CHARACTERS],
            'alias' => [
                'bail', 'sometimes', 'string', 'min:'.Store::ALIAS_MIN_CHARACTERS, 'max:'.Store::ALIAS_MAX_CHARACTERS,
                Rule::unique('stores')
            ],
            'call_to_action' => ['bail', 'sometimes', Rule::in(Store::CALL_TO_ACTION_OPTIONS())],
            'description' => ['bail', 'sometimes', 'nullable', 'min:'.Store::DESCRIPTION_MIN_CHARACTERS, 'max:'.Store::DESCRIPTION_MAX_CHARACTERS],
            'sms_sender_name' => [
                'bail', 'sometimes', 'nullable', 'min:'.Store::SMS_SENDER_NAME_MIN_CHARACTERS, 'max:'.Store::SMS_SENDER_NAME_MAX_CHARACTERS,
            ],
            'currency' => [
                'bail', 'sometimes', 'string', 'size:3',
                Rule::in(collect($this->supportedCurrencySymbols)->keys())
            ],
            'verified' => ['exclude'],
            'online' => ['bail', 'sometimes', 'boolean'],
            'offline_message' => ['bail', 'sometimes', 'string', 'min:'.Store::OFFLINE_MESSAGE_MIN_CHARACTERS, 'max:'.Store::OFFLINE_MESSAGE_MAX_CHARACTERS],
            'identified_orders' => ['bail', 'sometimes', 'boolean'],

            'social_links' => ['bail', 'sometimes', 'array'],
            'social_links.*.name' => ['bail', 'nullable', 'string', 'min:'.Store::SOCIAL_LINK_NAME_MIN_CHARACTERS, 'max:'.Store::SOCIAL_LINK_NAME_MAX_CHARACTERS],
            'social_links.*.link' => ['bail', 'nullable', 'url:http,https'],

            'show_opening_hours' => ['bail', 'sometimes', 'boolean'],
            'allow_checkout_on_closed_hours' => ['bail', 'sometimes', 'boolean'],
            'opening_hours' => ['bail', 'sometimes', 'array', 'size:7'],
            'opening_hours.*.available' => ['bail', 'boolean'],
            'opening_hours.*.hours' => ['bail', 'array'],
            'opening_hours.*.hours.*' => ['bail', 'array'],
            'opening_hours.*.hours.*.*' => ['bail', 'string', 'regex:/^(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9])$/'],

            'checkout_fees' => ['bail', 'sometimes', 'array', 'max:5'],
            'checkout_fees.name' => ['bail', 'required', 'string', 'min:'.Store::CHECKOUT_FEE_NAME_MIN_CHARACTERS, 'max:'.Store::CHECKOUT_FEE_NAME_MAX_CHARACTERS],
            'checkout_fees.type' => ['bail', 'required', Rule::in(Store::CHECKOUT_FEE_TYPES())],
            'checkout_fees.flat_rate' => ['bail', 'required_without:checkout_fees.percentage_rate', 'min:0', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'checkout_fees.percentage_rate' => ['bail', 'required_without:checkout_fees.flat_rate', 'min:0', 'max:100', 'numeric'],

            'email' => ['bail', 'nullable', 'sometimes', 'email'],
            'ussd_mobile_number' => ['bail', 'nullable', 'sometimes', 'string', 'phone'],
            'contact_mobile_number' => ['bail', 'nullable', 'sometimes', 'string', 'phone'],
            'whatsapp_mobile_number' => ['bail', 'nullable', 'sometimes', 'string', 'phone'],

            'country' => ['bail', 'sometimes', Rule::in(collect((new CountryService)->getCountries())->map(fn($country) => $country->iso)->toArray())],
            'currency' => ['bail', 'sometimes', Rule::in(collect((new CurrencyService)->getCurrencies())->map(fn($currency) => $currency['code'])->toArray())],
            'language' => ['bail', 'sometimes', Rule::in(collect((new LanguageService)->getLanguages())->map(fn($language) => $language['code'])->toArray())],
            'distance_unit' => ['bail', 'sometimes', Rule::in(Store::DISTANCE_UNIT_OPTIONS())],
            'tax_method' => ['bail', 'sometimes', Rule::in(Store::TAX_METHOD_OPTIONS())],
            'tax_id' => ['bail', 'sometimes', 'nullable', 'string', 'min:'.Store::TAX_ID_MIN_CHARACTERS, 'max:'.Store::TAX_ID_MAX_CHARACTERS],
            'tax_percentage_rate' => ['bail', 'sometimes', 'min:0', 'max:100', 'numeric'],

            'delivery_note' => ['bail', 'sometimes', 'nullable', 'min:'.Store::DELIVERY_NOTE_MIN_CHARACTERS, 'max:'.Store::DELIVERY_NOTE_MAX_CHARACTERS],
            'allow_delivery' => ['bail', 'sometimes', 'boolean'],
            'allow_free_delivery' => ['bail', 'sometimes', 'boolean'],
            'delivery_flat_fee' => ['bail', 'sometimes', 'min:0', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'delivery_destinations' => ['bail', 'sometimes', 'array'],
            'delivery_destinations.*.name' => ['bail', 'required', 'string', 'min:'.Store::DELIVERY_DESTINATION_NAME_MIN_CHARACTERS, 'max:'.Store::DELIVERY_DESTINATION_NAME_MAX_CHARACTERS],
            'delivery_destinations.*.allow_free_delivery' => ['bail', 'required', 'boolean'],
            'delivery_destinations.*.cost' => ['bail', 'required', 'min:0', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],

            'pickup_note' => ['bail', 'sometimes', 'nullable', 'min:'.Store::PICKUP_NOTE_MIN_CHARACTERS, 'max:'.Store::PICKUP_NOTE_MAX_CHARACTERS],
            'allow_pickup' => ['bail', 'sometimes', 'boolean'],
            'pickup_destinations' => ['bail', 'sometimes', 'array'],
            'pickup_destinations.*.name' => ['bail', 'required', 'string', 'min:'.Store::PICKUP_DESTINATION_NAME_MIN_CHARACTERS, 'max:'.Store::PICKUP_DESTINATION_NAME_MAX_CHARACTERS],
            'pickup_destinations.*.address' => ['bail', 'nullable', 'min:'.Store::PICKUP_DESTINATION_ADDRESS_MIN_CHARACTERS, 'max:'.Store::PICKUP_DESTINATION_ADDRESS_MAX_CHARACTERS],

            'allow_deposit_payments' => ['bail', 'sometimes', 'boolean'],
            'deposit_percentages' => ['bail', 'sometimes', 'array'],
            'deposit_percentages.*' => ['bail', 'required', 'integer', 'min:5', 'max:95'],

            'allow_installment_payments' => ['bail', 'sometimes', 'boolean'],
            'installment_percentages' => ['bail', 'sometimes', 'array'],
            'installment_percentages.*' => ['bail', 'required', 'integer', 'min:5', 'max:95'],

            'supported_payment_methods' => ['bail', 'sometimes', 'array'],
            'supported_payment_methods.*.id' => [
                'bail', 'required', 'uuid', Rule::exists('payment_methods')
            ],
            'supported_payment_methods.*.active' => ['bail', 'required', 'boolean'],
            'supported_payment_methods.*.instruction' => ['bail', 'nullable', 'string'],
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
            'call_to_action.in' => 'Answer "'.collect(Store::CALL_TO_ACTION_OPTIONS())->join('", "', '" or "').' to indicate the call to action',
            'logo.max' => 'The :attribute must not be greater than 4 megabytes',
            'cover_photo.max' => 'The :attribute must not be greater than 4 megabytes',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'social_links.*.name' => 'social name',
            'social_links.*.link' => 'social link',
            'opening_hours.*.available' => 'availability status',
            'opening_hours.*.hours' => 'open hours',
            'opening_hours.*.hours.*.*' => 'time',
        ];
    }
}
