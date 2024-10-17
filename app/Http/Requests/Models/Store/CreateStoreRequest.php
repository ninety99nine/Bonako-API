<?php

namespace App\Http\Requests\Models\Store;

use App\Models\Store;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
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
        $callToActionOptions = collect(Store::CALL_TO_ACTION_OPTIONS())->map(fn($filter) => strtolower($filter));

        return [
            'emoji' => ['bail', 'sometimes', 'nullable', 'string'],
            'logo' => ['bail', 'nullable', 'mimetypes:image/jpeg,image/png,image/jpg,image/gif,image/bmp', 'max:4096'],
            'cover_photo' => ['bail', 'nullable', 'mimetypes:image/jpeg,image/png,image/jpg,image/gif,image/bmp', 'max:4096'],
            'name' => ['bail', 'required', 'string', 'min:'.Store::NAME_MIN_CHARACTERS, 'max:'.Store::NAME_MAX_CHARACTERS],
            'alias' => [
                'bail', 'sometimes', 'required', 'string', 'min:'.Store::ALIAS_MIN_CHARACTERS, 'max:'.Store::ALIAS_MAX_CHARACTERS,
                Rule::unique('stores')
            ],
            'call_to_action' => ['bail', 'sometimes', 'required', Rule::in($callToActionOptions)],
            'description' => ['bail', 'sometimes', 'nullable', 'min:'.Store::DESCRIPTION_MIN_CHARACTERS, 'max:'.Store::DESCRIPTION_MAX_CHARACTERS],
            'sms_sender_name' => [
                'bail', 'sometimes', 'nullable', 'string', 'min:'.Store::SMS_SENDER_NAME_MIN_CHARACTERS, 'max:'.Store::SMS_SENDER_NAME_MAX_CHARACTERS,
            ],
            'currency' => [
                'bail', 'sometimes', 'required', 'string', 'size:3',
                Rule::in(collect($this->supportedCurrencySymbols)->keys())
            ],
            'online' => ['bail', 'sometimes', 'required', 'boolean'],
            'offline_message' => ['bail', 'sometimes', 'required', 'string', 'min:'.Store::OFFLINE_MESSAGE_MIN_CHARACTERS, 'max:'.Store::OFFLINE_MESSAGE_MAX_CHARACTERS],
            'identified_orders' => ['bail', 'sometimes', 'required', 'boolean'],


            'ussd_mobile_number' => ['bail', 'required', 'phone'],
            //'contact_mobile_number' => ['bail', 'required', 'phone'],
            //'whatsapp_mobile_number' => ['bail', 'required', 'phone'],
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
        return [];
    }
}
