<?php

namespace App\Http\Requests\Models\ShoppingCart;

use App\Enums\DeliveryMethodScheduleType;
use App\Models\Store;
use App\Models\Customer;
use App\Traits\AuthTrait;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;

class InspectShoppingCartRequest extends FormRequest
{
    use AuthTrait;

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

            //  Make sure that the "cart_products" is an array if provided
            if($this->has('cart_products') && is_string($this->request->all()['cart_products'])) {
                $this->merge([
                    'cart_products' => json_decode($this->request->all()['cart_products'])
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
        $rules = [
            'store_id' => ['required', 'uuid', Rule::exists('stores', 'id')],
            'guest_id' => [Rule::requiredIf(!$this->hasAuthUser()), 'uuid'],
            'cart_products' => ['array'],
            'cart_products.*' => ['required', 'array'],
            'cart_products.*.id' => ['required', 'uuid'],
            'cart_products.*.quantity' => ['required', 'numeric', 'min:1'],
            'cart_coupon_code' => ['bail', 'nullable', 'string'],
            'tip_flat_rate' => ['bail', 'nullable', 'min:1', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'tip_percentage_rate' => ['bail', 'nullable', 'min:1', 'max:100', 'numeric'],
            'delivery_method_id' => ['uuid', Rule::exists('delivery_methods', 'id')->where(function (Builder $query) {
                $query->where('store_id', request()->input('store_id'))->where('active', 1);
            })]
        ];

        $store = request()->has('store_id') ? Store::find(request()->input('store_id')) : null;

        if($store) {

            $store->collect_customer_first_name = true;
            $store->customer_first_name_optional = true;

            $store->collect_customer_last_name = true;
            $store->customer_last_name_optional = true;

            $store->collect_customer_email = true;
            $store->customer_email_optional = true;

            $store->collect_customer_mobile_number = true;
            $store->customer_mobile_number_optional = true;

            $store->collect_customer_birthday = true;
            $store->customer_birthday_optional = true;

            $requiresCustomerInformation =
                $store->collect_customer_first_name || $store->collect_customer_last_name ||
                $store->collect_customer_email || $store->collect_customer_mobile_number ||
                $store->collect_customer_birthday;

            $validate = $requiresCustomerInformation;

            $rules = array_merge($rules, $validate ? [
                'customer' => ['array']
            ] : ['exclude']);

            $validate = $store->collect_customer_first_name;

            $rules = array_merge($rules, $validate ? [
                'customer.first_name' => ['bail', Rule::requiredIf(!$store->customer_first_name_optional), 'string', 'min:'.Customer::FIRST_NAME_MIN_CHARACTERS, 'max:'.Customer::FIRST_NAME_MAX_CHARACTERS],
            ] : ['exclude']);

            $validate = $store->collect_customer_last_name;

            $rules = array_merge($rules, $validate ? [
                'customer.last_name' => ['bail', Rule::requiredIf(!$store->customer_last_name_optional), 'string', 'min:'.Customer::LAST_NAME_MIN_CHARACTERS, 'max:'.Customer::LAST_NAME_MAX_CHARACTERS],
            ] : ['exclude']);

            $validate = $store->collect_customer_email;

            $rules = array_merge($rules, $validate ? [
                'customer.email' => ['bail', Rule::requiredIf(!$store->customer_email_optional), 'email'],
            ] : ['exclude']);

            $validate = $store->collect_customer_mobile_number;

            $rules = array_merge($rules, $validate ? [
                'customer.mobile_number' => ['bail', Rule::requiredIf(!$store->customer_mobile_number_optional), 'phone'],
            ] : ['exclude']);

            $validate = $store->collect_customer_birthday;

            $rules = array_merge($rules, $validate ? [
                'customer.birthday' => ['bail', Rule::requiredIf(!$store->customer_birthday_optional), 'date', 'before:today'],
            ] : ['exclude']);

            $deliveryMethod = request()->has('delivery_method_id') ? $store->deliveryMethods()->active()->find(request()->input('delivery_method_id')) : null;

            if($deliveryMethod) {

                $deliveryMethod->set_schedule = true;
                $deliveryMethod->schedule_type = DeliveryMethodScheduleType::DATE_AND_TIME->value;

                $validate = $deliveryMethod->set_schedule;

                $rules = array_merge($rules, $validate ? [
                    'delivery_date' => ['bail', 'date', 'after_or_equal:today']
                ] : ['exclude']);

                $validate = $deliveryMethod->set_schedule && $deliveryMethod->schedule_type == DeliveryMethodScheduleType::DATE_AND_TIME->value;

                $rules = array_merge($rules, $validate ? [
                    'delivery_timeslot' => ['bail', 'string', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d - (?:[01]\d|2[0-3]):[0-5]\d$/']
                ] : ['exclude']);

            }

        }

        //  throw ValidationException::withMessages(['ussd_token' => 'The ussd token must be a string']);

        return $rules;
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
            'customer.email' => 'email',
            'customer.birthday' => 'birthday',
            'customer.last_name' => 'last name',
            'customer.first_name' => 'first name',
            'customer.mobile_number' => 'mobile number',



            'guest_id' => 'guest ID',
            'cart_products' => 'cart products',
            'cart_products.*' => 'cart products',
            'cart_products.*.id' => 'cart product id',
            'cart_products.*.quantity' => 'cart product quantity',
            'cart_coupon_code' => 'cart coupon code',
            'delivery_method_id' => 'delivery method',
        ];
    }
}
