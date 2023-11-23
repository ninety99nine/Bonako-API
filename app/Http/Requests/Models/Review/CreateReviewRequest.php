<?php

namespace App\Http\Requests\Models\Review;

use App\Models\Review;
use Illuminate\Support\Str;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CreateReviewRequest extends FormRequest
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
         *  Convert the "subject" to the correct format if it has been set on the request inputs
         *
         *  Example: convert "customerSupport" or "Customer Support" into "customer support"
         */
        if($this->request->has('subject')) {
            $this->merge([
                'subject' => $this->separateWordsThenLowercase($this->request->get('subject'))
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
        $subjects = collect(Review::STORE_REVIEW_FILTERS)->map(fn($filter) => strtolower($filter));

        return [
            'subject' => ['bail', 'required', Rule::in($subjects)],
            'rating' => ['bail', 'required', 'string', 'min:'.Review::RATING_MIN, 'max:'.Review::RATING_MAX],
            'comment' => ['bail', 'required_with:subject', 'string', 'min:'.Review::COMMENT_MIN_CHARACTERS, 'max:'.Review::COMMENT_MAX_CHARACTERS],
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
            'subject.in' => 'Answer "'.collect(Review::SUBJECTS)->join('", "', '" or "').'" to indicate the subject of concern',
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
