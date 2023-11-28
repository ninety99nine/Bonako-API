<?php

namespace App\Http\Requests\Models\User;

use App\Models\Review;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowUserReviewsRequest extends FormRequest
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
         *  Example: convert "Customer Service" or "customerService" into "customer service"
         */
        if($this->has('filter')) {
            $this->merge([
                'filter' => $this->separateWordsThenLowercase($this->get('filter'))
            ]);
        }

        /**
         *  Convert the "user_review_association" to the correct format if it has been set on the request inputs
         *
         *  Example: convert "teamMember" or "Team Member" into "team member"
         */
        if($this->has('user_review_association')) {
            $this->merge([
                'user_review_association' => $this->separateWordsThenLowercase($this->get('user_review_association'))
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
        $filters = collect(Review::USER_REVIEW_FILTERS)->map(fn($filter) => strtolower($filter));
        $userReviewAssociations = collect(Review::USER_REVIEW_ASSOCIATIONS)->map(fn($userOrderAssociation) => $this->separateWordsThenLowercase($userOrderAssociation));

        return [
            'user_review_association' => ['required', 'string', Rule::in($userReviewAssociations)],
            'with_store' => ['bail', 'sometimes', 'required', 'boolean'],
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
            'filter.in' => 'Answer "'.collect(Review::USER_REVIEW_FILTERS)->join('", "', '" or "').' to filter reviews',
            'filter.string' => 'Answer "'.collect(Review::USER_REVIEW_FILTERS)->join('", "', '" or "').' to filter reviews',
            'user_order_association.in' => 'Answer "'.collect(Review::USER_REVIEW_ASSOCIATIONS)->join('", "', '" or "').' for user review association',
            'user_order_association.string' => 'Answer "'.collect(Review::USER_REVIEW_ASSOCIATIONS)->join('", "', '" or "').' for user review association',
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
