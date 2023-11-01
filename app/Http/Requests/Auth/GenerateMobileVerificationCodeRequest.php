<?php

namespace App\Http\Requests\Auth;

use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use App\Models\MobileVerification;
use Illuminate\Foundation\Http\FormRequest;

class GenerateMobileVerificationCodeRequest extends FormRequest
{
    use BaseTrait;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //  Everyone is authorized to make this request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        //  Check if this request is performed on a user route i.e "/users/{user}/..."
        $requestOnUserRoute = request()->routeIs('user.*');

        //  Check if this request is performed on an auth user route i.e "/auth/user/..."
        $requestOnAuthUserRoute = request()->routeIs('auth.user.*');

        //  Check if the mobile number is required for this request
        $requiresMobileNumber = !$requestOnUserRoute && !$requestOnAuthUserRoute;

        /**
         *  If the request is performed on the user route or the auth user route,
         *  then the mobile number is not required, however if this request is
         *  performed on any other route then we must require the mobile
         *  number.
         */
        return $requiresMobileNumber ? [
            'mobile_number' => ['bail', 'required', 'string', 'starts_with:267', 'regex:/^[0-9]+$/', 'size:11']
        ] : [];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'mobile_number.regex' => 'The mobile number must only contain numbers',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [

        ];
    }
}
