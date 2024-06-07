<?php

namespace App\Http\Requests\Models\Store;

use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Pivots\UserStoreAssociation;

class UpdateFollowingRequest extends FormRequest
{
    use BaseTrait;

    public $statuses = ['Following', 'Unfollowed'];

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
             *  Convert the "status" to the correct format if it has been set on the request inputs
             *
             *  Example: convert "following" or "Following" into "Following"
             */
            if($this->has('status')) {
                $this->merge([
                    'status' => $this->separateWordsThenLowercase($this->get('status'))
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
        $statuses = collect($this->statuses)->map(fn($filter) => strtolower($filter));

        return [
            'status' => ['bail', 'sometimes', 'required', Rule::in($statuses)],
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
            'status.string' => 'Answer "'.collect($this->statuses)->join('", "', '" or "').' to update following status',
            'status.in' => 'Answer "'.collect($this->statuses)->join('", "', '" or "').' to update following status',
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
