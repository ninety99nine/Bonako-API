<?php

namespace App\Http\Requests\Models\Coupon;

use App\Models\Coupon;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCouponRequest extends FormRequest
{
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
         *  Convert the "discount_type" to the correct format if it has been set on the request inputs
         *
         *  Example: convert "percentage" into "Percentage"
         */
        if($this->request->has('discount_type')) {
            $this->merge([
                'discount_type' => strtolower($this->request->all()['discount_type'])
            ]);
        }

        //  Make sure that the "hours_of_day" is an array if provided
        if($this->request->has('hours_of_day') && is_string($this->request->all()['hours_of_day'])) {
            $this->merge([
                'hours_of_day' => json_decode($this->request->all()['hours_of_day'])
            ]);
        }

        //  Make sure that the "days_of_the_week" is an array if provided
        if($this->request->has('days_of_the_week') && is_string($this->request->all()['days_of_the_week'])) {
            $this->merge([
                'days_of_the_week' => json_decode($this->request->all()['days_of_the_week'])
            ]);
        }

        //  Make sure that the "days_of_the_month" is an array if provided
        if($this->request->has('days_of_the_month') && is_string($this->request->all()['days_of_the_month'])) {
            $this->merge([
                'days_of_the_month' => json_decode($this->request->all()['days_of_the_month'])
            ]);
        }

        //  Make sure that the "months_of_the_year" is an array if provided
        if($this->request->has('months_of_the_year') && is_string($this->request->all()['months_of_the_year'])) {
            $this->merge([
                'months_of_the_year' => json_decode($this->request->all()['months_of_the_year'])
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
        $discountTypes = collect(Coupon::DISCOUNT_TYPES)->map(fn($discountType) => strtolower($discountType));

        return [

            /*  General Information  */
            'name' => [
                'bail', 'sometimes', 'required', 'string', 'min:'.Coupon::NAME_MIN_CHARACTERS, 'max:'.Coupon::NAME_MAX_CHARACTERS,
                //  Make sure that this coupon name does not already exist for the same store
                Rule::unique('coupons')->where('store_id', request()->coupon->store->id)->ignore(request()->coupon->id)
            ],
            'active' => ['bail', 'sometimes', 'required', 'boolean'],
            'description' => ['bail', 'sometimes', 'required', 'string', 'min:'.Coupon::DESCRIPTION_MIN_CHARACTERS, 'max:'.Coupon::DESCRIPTION_MAX_CHARACTERS],

            /*  Offer Discount Information  */
            'offer_discount' => ['bail', 'sometimes', 'required', 'boolean'],
            'discount_type' => ['bail', 'sometimes', 'required', Rule::in($discountTypes)],
            'discount_percentage_rate' => ['bail', 'sometimes', 'required', 'min:1', 'max:100', 'numeric'],
            'discount_fixed_rate' => ['bail', 'sometimes', 'required', 'min:1', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],

            /*  Offer Delivery Information  */
            'offer_free_delivery' => ['bail', 'sometimes', 'required', 'boolean'],

            /*  Code Activation Information  */
            'activate_using_code' => ['bail', 'sometimes', 'required', 'boolean'],
            'code' => ['bail', 'sometimes', 'required', 'string', 'min:'.Coupon::CODE_MIN_CHARACTERS, 'max:'.Coupon::CODE_MAX_CHARACTERS],

            /*  Grand Total Activation Information  */
            'activate_using_minimum_grand_total' => ['bail', 'sometimes', 'required', 'boolean'],
            'minimum_grand_total' => ['bail', 'sometimes', 'required', 'min:0', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],

            /*  Minimum Total Products Activation Information  */
            'activate_using_minimum_total_products' => ['bail', 'sometimes', 'required', 'boolean'],
            'minimum_total_products' => ['bail', 'sometimes', 'required', 'min:0', 'numeric'],

            /*  Minimum Total Products Activation Information  */
            'activate_using_minimum_total_product_quantities' => ['bail', 'sometimes', 'required', 'boolean'],
            'minimum_total_product_quantities' => ['bail', 'sometimes', 'required', 'min:0', 'numeric'],

            /*  Start Datetime Activation Information  */
            'activate_using_start_datetime' => ['bail', 'sometimes', 'required', 'boolean'],
            'start_datetime' => ['bail', 'sometimes', 'required', 'date'],

            /*  End Datetime Activation Information  */
            'activate_using_end_datetime' => ['bail', 'sometimes', 'required', 'boolean'],
            'end_datetime' => ['bail', 'sometimes', 'required', 'date'],

            /*  Hours Of Day Activation Information  */
            'activate_using_hours_of_day' => ['bail', 'sometimes', 'required', 'boolean'],
            'hours_of_day' => ['bail', 'sometimes', 'required', 'array'],

            /*  Days Of The Week Activation Information  */
            'activate_using_days_of_the_week' => ['bail', 'sometimes', 'required', 'boolean'],
            'days_of_the_week' => ['bail', 'sometimes', 'required', 'array'],

            /*  Days Of The Month Activation Information  */
            'activate_using_days_of_the_month' => ['bail', 'sometimes', 'required', 'boolean'],
            'days_of_the_month' => ['bail', 'sometimes', 'required', 'array'],

            /*  Months Of The Year Activation Information  */
            'activate_using_months_of_the_year' => ['bail', 'sometimes', 'required', 'boolean'],
            'months_of_the_year' => ['bail', 'sometimes', 'required', 'array'],

            /*  Usage Activation Information  */
            'activate_using_usage_limit' => ['bail', 'sometimes', 'required', 'boolean'],
            'remaining_quantity' => ['bail', 'sometimes', 'required', 'min:1', 'max:'.Coupon::REMAINING_QUANTITY_MAX, 'numeric'],

            /*  Customer Activation Information  */
            'activate_for_existing_customer' => ['bail', 'sometimes', 'required', 'boolean'],
            'activate_for_new_customer' => ['bail', 'sometimes', 'required', 'boolean'],
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
            'discount_type.in' => 'Answer "'.collect(Coupon::DISCOUNT_TYPES)->join('", "', '" or "').'" to indicate the coupon discount type',
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
