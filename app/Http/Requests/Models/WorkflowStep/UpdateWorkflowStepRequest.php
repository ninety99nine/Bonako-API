<?php

namespace App\Http\Requests\Models\WorkflowStep;

use App\Models\WorkflowStep;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkflowStepRequest extends FormRequest
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
        $workflow = WorkflowStep::find(request()->workflowStepId);

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
                    'settings.auto_cancel' => ['required', 'boolean'],
                    'settings.reminder_delay_value' => ['bail', 'required', 'integer', 'min:1'],
                    'settings.reminder_delay_units' => ['bail', 'required', Rule::in(['minute', 'hour'])],
                    'settings.cancel_delay_value' => ['bail', 'required_if_accepted:settings.auto_cancel', 'integer', 'min:1'],
                    'settings.cancel_delay_units' => ['bail', 'required_if_accepted:settings.auto_cancel', Rule::in(['minute', 'hour'])],
                ]);

            }

        }

        return array_merge([
            'return' => ['sometimes', 'boolean'],
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
            'settings.auto_cancel' => 'auto cancel',
            'settings.mobile_numbers' => 'mobile numbers',
            'settings.mobile_numbers.*' => 'mobile number',
            'settings.cancel_delay_value' => 'cancel delay value',
            'settings.cancel_delay_units' => 'cancel delay units',
            'settings.reminder_delay_value' => 'cancel reminder value',
            'settings.reminder_delay_units' => 'cancel reminder units',
        ];
    }
}
