<?php

namespace App\Http\Requests\Models\Workflow;

use App\Models\Workflow;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkflowRequest extends FormRequest
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
            'active' => ['sometimes', 'boolean'],
            'name' => [
                'bail', 'required', 'string', 'min:'.Workflow::NAME_MIN_CHARACTERS, 'max:'.Workflow::NAME_MAX_CHARACTERS,
                Rule::unique('workflows')->where('store_id', request()->input('store_id'))
            ],
            'trigger' => ['bail', 'required', Rule::in(Workflow::WORKFLOW_TRIGGER_TYPES())],
            'resource' => ['bail', 'required', Rule::in(Workflow::WORKFLOW_RESOURCE_TYPES())]
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
