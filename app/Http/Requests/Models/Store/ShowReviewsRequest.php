<?php

namespace App\Http\Requests\Models\Store;

use App\Models\Review;
use Illuminate\Support\Str;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowReviewsRequest extends FormRequest
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
         *  Convert the "filter" to the correct format if it has been set on the request inputs
         *
         *  Example: convert "customerSupport" or "Customer Support" into "customer support"
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
        $filters = collect(Review::FILTERS)->map(fn($filter) => strtolower($filter));

        return [
            'user_id' => ['sometimes', 'required', 'integer', 'numeric', 'min:1'],
            'with_user' => ['bail', 'sometimes', 'required', 'boolean'],
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
            'filter.string' => 'Answer "'.collect(Review::FILTERS)->join('", "', '" or "').' to show filtered reviews',
            'filter.in' => 'Answer "'.collect(Review::FILTERS)->join('", "', '" or "').' to show filtered reviews',
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
