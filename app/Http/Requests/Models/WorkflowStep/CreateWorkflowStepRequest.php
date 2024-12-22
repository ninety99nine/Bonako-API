<?php

namespace App\Http\Requests\Models\WorkflowStep;

use App\Models\WorkflowStep;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CreateWorkflowStepRequest extends FormRequest
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
        $rules = [];
        $workflow = WorkflowStep::find(request()->input('workflow_id'));

        if($workflow) {

            $trigger = $workflow->trigger;
            $resource = $workflow->resource;
            $action = request()->input('settings.action');
            $recipient = request()->input('settings.recipient');

            if(in_array($trigger, [
                'waiting','cancelled','completed','on its way','ready for pickup',
                'paid','unpaid','partially paid','pending payment','low stock'
            ])) {

                $rules = [
                    'settings.action' => ['bail', 'required', Rule::in(['whatsapp', 'email', 'sms'])],
                    'settings.recipient' => ['bail', 'required', Rule::in(['team', 'customer'])],
                ];

            }

            if($recipient == 'team') {

                $rules = array_merge($rules, [
                    'settings.mobile_numbers.*' => ['bail', 'string', 'phone'],
                    'settings.email' => ['bail', 'required_if:settings.action,email', 'email'],
                    'settings.mobile_numbers' => ['bail', 'required_if:settings.action,whatsapp', 'array'],
                ]);

            }else if($recipient == 'customer') {

                if(in_array($action, ['whatsapp', 'email'])) {

                    $rules = array_merge($rules, [
                        'settings.note' => ['bail', 'string', 'min:1', 'max: 160']
                    ]);

                }

            }

            if($trigger == 'pending payment') {

                $rules = array_merge($rules, [
                    'settings.add_delay' => ['required', 'boolean'],
                    'settings.delay_time_value' => ['bail', 'required', 'integer', 'min:1'],
                    'settings.delay_time_unit' => ['bail', 'required', Rule::in(['minute', 'hour'])],

                    'settings.auto_cancel' => ['required', 'boolean'],
                    'settings.cancel_time_value' => ['bail', 'required_if_accepted:settings.auto_cancel', 'integer', 'min:1'],
                    'settings.cancel_time_unit' => ['bail', 'required_if_accepted:settings.auto_cancel', Rule::in(['minute', 'hour'])],
                ]);

            }

        }

        return array_merge([
            'return' => ['sometimes', 'boolean'],
            'workflow_id' => ['required', 'uuid'],
            'settings' => ['bail', 'required', 'array'],
        ], $rules);
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
        return [
            'settings.note' => 'note',
            'settings.email' => 'email',
            'settings.action' => 'action',
            'settings.recipient' => 'recipient',
            'settings.add_delay' => 'add delay',
            'settings.auto_cancel' => 'auto cancel',
            'settings.mobile_numbers' => 'mobile numbers',
            'settings.mobile_numbers.*' => 'mobile number',
            'settings.cancel_time_unit' => 'cancel delay units',
            'settings.cancel_time_value' => 'cancel delay value',
            'settings.delay_time_unit' => 'cancel reminder units',
            'settings.delay_time_value' => 'cancel reminder value',
        ];
    }
}
