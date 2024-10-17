<?php

namespace App\Http\Requests\Models\SmsMessage;

use App\Models\SmsMessage;
use App\Traits\Base\BaseTrait;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSmsMessageRequest extends FormRequest
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
            'send_message' => ['sometimes', 'boolean'],
            'recipient_mobile_number' => ['bail', 'sometimes', 'required', 'phone'],
            'content' => ['bail', 'sometimes', 'required', 'string', 'min:'.SmsMessage::CONTENT_MIN_CHARACTERS, 'max:'.SmsMessage::CONTENT_MAX_CHARACTERS]
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
