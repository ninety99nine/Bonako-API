<?php

namespace App\Http\Requests\Models\Product;

use App\Models\Product;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
        try {
            /**
             *  Convert the "allowed_quantity_per_order" to the correct format if it has been set on the request inputs
             *
             *  Example: convert "unlimited" into "Unlimited"
             */
            if($this->has('allowed_quantity_per_order')) {
                $this->merge([
                    'allowed_quantity_per_order' => strtolower($this->get('allowed_quantity_per_order'))
                ]);
            }

            /**
             *  Convert the "stock_quantity_type" to the correct format if it has been set on the request inputs
             *
             *  Example: convert "unlimited" into "Unlimited"
             */
            if($this->has('stock_quantity_type')) {
                $this->merge([
                    'stock_quantity_type' => strtolower($this->get('stock_quantity_type'))
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
        $moneyRules = ['bail', 'required', 'min:0', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'];
        $stockQuantityType = collect(Product::STOCK_QUANTITY_TYPE)->map(fn($option) => strtolower($option));
        $allowedQuantityPerOrder = collect(Product::ALLOWED_QUANTITY_PER_ORDER)->map(fn($option) => strtolower($option));
        $wantsToShowDescription = request()->filled('show_description') && $this->isTruthy(request()->input('show_description'));

        return [
            'return' => ['sometimes', 'boolean'],

            /*  General Information  */
            'name' => [
                'bail', 'sometimes', 'required', 'string', 'min:'.Product::NAME_MIN_CHARACTERS, 'max:'.Product::NAME_MAX_CHARACTERS,
                /**
                 *  Make sure that this product name does not already exist on the same store
                 *  (Except for the same product)
                 */
                Rule::unique('products')->where('store_id', request()->storeId)->ignore(request()->productId)
            ],
            'visible' => ['bail', 'sometimes', 'required', 'boolean'],
            'show_description' => ['bail', 'sometimes', 'required', 'boolean'],
            'description' => ['bail', 'sometimes', Rule::requiredIf($wantsToShowDescription), 'nullable', 'min:'.Product::DESCRIPTION_MIN_CHARACTERS, 'max:'.Product::DESCRIPTION_MAX_CHARACTERS],

            /*  Tracking Information  */
            'sku' => ['bail', 'sometimes', 'nullable', 'string', 'min:'.Product::SKU_MIN_CHARACTERS, 'max:'.Product::SKU_MAX_CHARACTERS],
            'barcode' => ['bail', 'sometimes', 'nullable', 'string', 'min:'.Product::BARCODE_MIN_CHARACTERS, 'max:'.Product::BARCODE_MAX_CHARACTERS],

            /*  Variation Information
             *
             *  variant_attributes: Exclude from the request data returned
             *      - Only modifiable on creation of variations
             */
            'allow_variations' => ['bail', 'sometimes', 'required', 'boolean'],
            'variant_attributes' => ['exclude'],
            'total_variations' => ['exclude'],
            'total_visible_variations' => ['exclude'],

            /*  Pricing Information
             *
             *  currency: Exclude from the request data returned
             *      - The currency is derived from the store itself
            */
            'is_free' => ['bail', 'sometimes', 'required', 'boolean'],
            'currency' => ['exclude'],
            'unit_regular_price' => collect($moneyRules)->add('sometimes')->toArray(),
            'unit_sale_price' => collect($moneyRules)->add('sometimes')->toArray(),
            'unit_cost_price' => collect($moneyRules)->add('sometimes')->toArray(),

            /*  Quantity Information  */
            'allowed_quantity_per_order' => ['bail', 'sometimes', 'required', 'string', Rule::in($allowedQuantityPerOrder)],
            'maximum_allowed_quantity_per_order' => [
                'bail', 'sometimes', 'integer', 'min:'.Product::MAXIMUM_ALLOWED_QUANTITY_PER_ORDER_MIN, 'max:'.Product::MAXIMUM_ALLOWED_QUANTITY_PER_ORDER_MAX,
                Rule::requiredIf(fn() => request()->input('allowed_quantity_per_order') === 'limited')
            ],

            /*  Stock Information  */
            'stock_quantity_type' => ['bail', 'sometimes', 'required', 'string', Rule::in($stockQuantityType)],
            'stock_quantity' => [
                'bail', 'sometimes', 'integer', 'min:'.Product::STOCK_QUANTITY_MIN, 'max:'.Product::STOCK_QUANTITY_MAX,
                Rule::requiredIf(fn() => strtolower(request()->input('stock_quantity_type')) === 'limited')
            ],
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
            'allowed_quantity_per_order.string' => 'Answer "'.collect(Product::ALLOWED_QUANTITY_PER_ORDER)->join('", "', '" or "').'" to indicate the allowed quantity per order',
            'allowed_quantity_per_order.in' => 'Answer "'.collect(Product::ALLOWED_QUANTITY_PER_ORDER)->join('", "', '" or "').'" to indicate the allowed quantity per order',
            'stock_quantity_type.string' => 'Answer "'.collect(Product::STOCK_QUANTITY_TYPE)->join('", "', '" or "').'" to indicate the stock quantity type',
            'stock_quantity_type.in' => 'Answer "'.collect(Product::STOCK_QUANTITY_TYPE)->join('", "', '" or "').'" to indicate the stock quantity type',
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
