<?php

namespace App\Http\Requests\Models\Store;

use App\Models\Subscription;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowSubscriptionsRequest extends FormRequest
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
             *  Convert the "filter" to the correct format if it has been set on the request inputs
             *
             *  Example: convert "Inactive" or "inActive" into "inactive"
             */
            if($this->has('filter')) {
                $this->merge([
                    'filter' => $this->separateWordsThenLowercase($this->get('filter'))
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
        $filters = collect(Subscription::FILTERS)->map(fn($filter) => $this->separateWordsThenLowercase($filter));

        return [
            'filter' => ['sometimes', 'string', Rule::in($filters)],
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
            'filter.string' => 'Answer "'.collect(Subscription::FILTERS)->join('", "', '" or "').' to filter subscriptions',
            'filter.in' => 'Answer "'.collect(Subscription::FILTERS)->join('", "', '" or "').' to filter subscriptions',
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
