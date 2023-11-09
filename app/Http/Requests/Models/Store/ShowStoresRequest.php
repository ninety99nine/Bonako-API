<?php

namespace App\Http\Requests\Models\Store;

use App\Models\Store;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowStoresRequest extends FormRequest
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
        /**
         *  Convert the filter to the correct format if it has been set on the request inputs
         *
         *  Example: convert "popularToday" or "Popular Today" into "popular today"
         */
        if($this->request->has('filter')) {
            $this->merge([
                'filter' => $this->separateWordsThenLowercase($this->request->get('filter'))
            ]);
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
        $filters = collect(Store::STORE_FILTERS)->map(fn($filter) => $this->separateWordsThenLowercase($filter));

        return [
            'filter' => ['bail', 'sometimes', 'required', 'string', Rule::in($filters)],
            'with_count_active_subscriptions' => ['bail', 'sometimes', 'required', 'boolean'],
            'with_auth_active_subscription' => ['bail', 'sometimes', 'required', 'boolean'],
            'with_count_collected_orders' => ['bail', 'sometimes', 'required', 'boolean'],
            'with_count_team_members' => ['bail', 'sometimes', 'required', 'boolean'],
            'with_visible_products' => ['bail', 'sometimes', 'required', 'boolean'],
            'with_visit_shortcode' => ['bail', 'sometimes', 'required', 'boolean'],
            'with_count_followers' => ['bail', 'sometimes', 'required', 'boolean'],
            'with_count_products' => ['bail', 'sometimes', 'required', 'boolean'],
            'with_count_coupons' => ['bail', 'sometimes', 'required', 'boolean'],
            'with_count_reviews' => ['bail', 'sometimes', 'required', 'boolean'],
            'with_count_orders' => ['bail', 'sometimes', 'required', 'boolean'],
            'with_orders' => ['bail', 'sometimes', 'required', 'boolean'],
            'with_rating' => ['bail', 'sometimes', 'required', 'boolean'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        $message = 'Answer "'.collect(array_map('ucwords', Store::STORE_FILTERS))->join('", "', '" or "').' to show specific types of stores';

        return [
            'filter.string' => $message,
            'filter.in' => $message
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
