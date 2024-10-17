<?php

namespace App\Http\Requests\Models\StoreQuota;

use App\Traits\Base\BaseTrait;
use Illuminate\Foundation\Http\FormRequest;

class CreateStoreQuotaRequest extends FormRequest
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
            'sms_credits' => ['bail', 'required', 'integer', 'min:0'],
            'email_credits' => ['bail', 'required', 'integer', 'min:0'],
            'whatsapp_credits' => ['bail', 'required', 'integer', 'min:0'],
            'store_id' => ['required', 'uuid']
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
