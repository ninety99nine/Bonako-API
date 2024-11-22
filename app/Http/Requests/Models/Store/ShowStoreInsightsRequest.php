<?php

namespace App\Http\Requests\Models\Store;

use App\Enums\Association;
use App\Models\Store;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowStoreInsightsRequest extends FormRequest
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
     *  We want to modify the request input before validating
     *
     *  Reference: https://laracasts.com/discuss/channels/requests/modify-request-input-value-before-validation
     */
    public function getValidatorInstance()
    {
        try {

            if($this->has('period')) {
                $this->merge(['period' => strtolower($this->request->all()['period'])]);
            }

            if($this->has('category')) {
                $this->merge(['category' => strtolower($this->request->all()['category'])]);
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
            'period' => ['bail', 'required', Rule::in(Store::INSIGHT_PERIODS())],
            'category' => ['bail', 'sometimes', Rule::in(Store::INSIGHT_CATEGORIES())],
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
