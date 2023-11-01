<?php

namespace App\Repositories;

use App\Enums\CanSaveChanges;
use App\Models\Product;
use App\Models\Variable;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Repositories\BaseRepository;
use App\Services\AWS\AWSService;

class ProductRepository extends BaseRepository
{
    protected $requiresConfirmationBeforeDelete = false;

    /**
     *  Eager load relationships on the given model
     *
     *  @param \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder $model
     *  @return ProductRepository
     */
    public function eagerLoadProductRelationships($model) {

        $relationships = [];
        $countableRelationships = [];

        if($model instanceof Product) {

            //  Additionally we can eager load the variables on this product
            array_push($relationships, 'variables');

        }

        //  Check if we want to eager load the variables on this product otherwise check if this product is a variation
        if( request()->input('with_variables') || ($model instanceof Product && $model->is_variation)) {

            //  Additionally we can eager load the variables on this product
            array_push($relationships, 'variables');

        }

        if( !empty($relationships) ) {

            $model = ($model instanceof Product)
                ? $model->load($relationships)->loadCount($countableRelationships)
                : $model->with($relationships)->withCount($countableRelationships);

        }

        $this->setModel($model);

        return $this;
    }

    /**
     *  Show the product photo
     *
     *  @return array
     */
    public function showPhoto() {

        return [
            'photo' => $this->model->photo
        ];

    }

    /**
     *  Update the product photo
     *
     *  @param \Illuminate\Http\Request $request
     *  @return array | ProductRepository
     */
    public function updatePhoto(Request $request) {

        //  Remove the exiting photo (if any) and save the new photo (if any)
        return $this->removeExistingPhoto(CanSaveChanges::NO)->storePhoto($request);

    }

    /**
     *  Remove the existing product photo
     *
     *  @param CanSaveChanges $canSaveChanges - Whether to save the product changes after deleting the photo
     *  @return array | ProductRepository
     */
    public function removeExistingPhoto($canSaveChanges = CanSaveChanges::YES) {

        /**
         *  @var Product $product
         */
        $product = $this->model;

        //  Check if we have an existing photo productd
        $hasExistingPhoto = !empty($product->photo);

        //  If the product has an existing photo productd
        if( $hasExistingPhoto ) {

            //  Delete the photo file
            AWSService::delete($product->photo);

        }

        //  If we should save these changes on the database
        if($canSaveChanges == CanSaveChanges::YES) {

            //  Save the product changes
            $this->update(['photo' => null]);

            return [
                'message' => 'Photo deleted successfully'
            ];

        //  If we should not save these changes on the database
        }else{

            //  Remove the photo url reference from the product
            $product->photo = null;

            //  Set the modified product
            $this->setModel($product);

        }

        return $this;

    }

    /**
     *  Store the product photo
     *
     *  @param \Illuminate\Http\Request $request
     *  @return array | ProductRepository
     */
    public function storePhoto(Request $request) {
        /**
         *  @var Product $product
         */
        $product = $this->model;

        //  Check if we have a new photo provided
        $hasNewPhoto = $request->hasFile('photo');

        /**
         *  Save the new photo when the following condition is satisfied:
         *
         *  1) The photo is provided when we are updating the photo only
         *
         *  If the photo is provided while creating or updating the product as
         *  a whole, then the photo will be updated with the rest of the
         *  product details as a single query.
         *
         *  Refer to the saving() method of the ProductObserver::class
         */
        $updatingTheProductPhotoOnly = $request->routeIs('product.photo.update');

        //  If we have a new photo provided
        if( $hasNewPhoto ) {

            //  Save the photo on AWS and update the product with the photo url
            $product->photo = AWSService::store('photos', $request->photo);

            //  Set the modified product
            $this->setModel($product);

            if( $updatingTheProductPhotoOnly ) {

                //  Save the product changes
                $product->save();

            }

        }

        if( $updatingTheProductPhotoOnly ) {

            //  Return the photo image url
            return ['photo' => $product->photo];

        }

        return $this;

    }

    /**
     *  Show the product
     */
    public function showProduct()
    {
        return $this->eagerLoadProductRelationships($this->model);
    }

    /**
     *  Update the product
     */
    public function updateProduct(Request $request)
    {
        //  Update this product
        parent::update($request);

        /// Check if this product is a variation of another product
        if($this->model->is_variation) {

            /**
             *  @var Product $parentProduct
             */
            $parentProduct = Product::find($this->model->parent_product_id);

            if($parentProduct) {

                $parentProduct->updateTotalVisibleVariations();

            }

        }

        return $this->eagerLoadProductRelationships($this->model);
    }

    /**
     *  Show the product variations
     */
    public function showVariations(Request $request)
    {
        $variations = $this->model->variations();

        // Convert "color|blue,size|small" into [" color|blue ", " size|small "]
        $variantAttributeChoices = explode(',', $request->input('variant_attribute_choices'));

        foreach($variantAttributeChoices as $variantAttributeChoice) {

            // Convert [" color|blue "] into [" color", "blue "]
            $variantAttributeChoice = explode('|', $variantAttributeChoice);

            // Convert [" color", "blue "] into ["color", "blue"]
            $variantAttributeChoice = array_map('trim', $variantAttributeChoice);

            if(isset($variantAttributeChoice[0]) && isset($variantAttributeChoice[1])) {

                //  Filter by the variable attribute choice
                $variations->whereHas('variables', function ($query) use ($variantAttributeChoice) {

                    $name = $variantAttributeChoice[0];
                    $value = $variantAttributeChoice[1];

                    //  Where "name"="color" and where "value"="blue"
                    $query->where('name', $name)->where('value', $value);

                });

            }

        }


        return $this->eagerLoadProductRelationships($variations)->get();
    }

    /**
     *  Create or update the product variations
     */
    public function createVariations(Request $request)
    {
        /**
         *  Get the variant attributes data e.g
         *
         *  $variantAttributes = [
         *    [
         *      'name' => 'Color',
         *      'values' => ["Red", "Green", "Blue"],
         *      'instruction' => 'Select color'
         *    ],
         *    [
         *      'name' => 'Size',
         *      'values' => ["L", "M", 'SM'],
         *      'instruction' => 'Select size'
         *    ]
         *  ]
         */
        $variantAttributes = $request->input('variant_attributes');

        /**
         *  Lets make sure that the first letter of every name is capitalized
         *  and that we have instructions foreach variant attribute otherwise
         *  set a default instruction.
         */
        $variantAttributes = collect($variantAttributes)->map(function($variantAttribute){

            $variantAttribute['name'] = ucfirst($variantAttribute['name']);

            if(!isset($variantAttribute['instruction']) || empty($variantAttribute['instruction'])) {
                $variantAttribute['instruction'] = 'Select option';
            }

            return $variantAttribute;

        });

        /**
         *  Restructure the variant attribute e.g
         *
         *  $variantAttributes = [
         *    "Color" => ["Red", "Green", "Blue"],
         *    "Size" => ["Large", "Medium", 'Small']
         *  ]
         */
        $variantAttributesRestructured = $variantAttributes->mapWithKeys(function($variantAttribute, $key) {
            return [$variantAttribute['name'] => $variantAttribute['values']];
        });

        /**
         *  Cross join the values of each variant attribute values to
         *  return a cartesian product with all possible permutations
         *
         * [
         *    ["Red","Large"],
         *    ["Red","Medium"],
         *    ["Red","Small"],
         *
         *    ["Green","Large"],
         *    ["Green","Medium"],
         *    ["Green","Small"],
         *
         *    ["Blue","Large"],
         *    ["Blue","Medium"],
         *    ["Blue","Small"]
         * ]
         *
         *  Cross join the variant attribute values into an Matrix
         */
        $variantAttributeMatrix = Arr::crossJoin(...$variantAttributesRestructured->values());

        //  Create the product variation templates
        $productVariationTemplates = collect($variantAttributeMatrix)->map(function($options) use ($variantAttributesRestructured) {

            /**
             *  Foreach matrix entry let us create a product variation template.
             *
             *  If the main product is called "Summer Dress" and the
             *
             *  $options = ["Red", "Large"]
             *
             *  Then the variation product is named using both the parent
             *  product name and the variation options. For example:
             *
             *  "Summer Dress (Red and Large)"
             */
            $name = $this->model->name.' ('.trim( collect($options)->map(fn($option) => ucfirst($option))->join(', ') ).')';

            $template = [
                'name' => $name,
                'user_id' => auth()->user()->id,
                'parent_product_id' => $this->model->id,
                'store_id' => $this->model->store_id,
                'created_at' => now(),
                'updated_at' => now(),

                //  Define the variable templates
                'variableTemplates' => collect($options)->map(function($option, $key) use ($variantAttributesRestructured) {

                    /**
                     *  $option = "Red" or "Large"
                     *
                     *  $variantAttributeNames = ["Color", "Size"]
                     *
                     *  $variantAttributeNames->get($key = 0) returns "Color"
                     *  $variantAttributeNames->get($key = 1) returns "Size"
                     */
                    $variantAttributeNames = $variantAttributesRestructured->keys();

                    return [
                        'name' => $variantAttributeNames->get($key),
                        'value' => $option
                    ];
                })
            ];

            return $template;

        });

        //  Get existing product variations and their respective variables
        $existingProductVariations = $this->model->variations()->with('variables')->get();

        /**
         *  Group the existing product variations into two groups:
         *
         *  (1) Those that have matching variant attributes (Must not be deleted)
         *  (2) Those that do not have matching variant attributes (Must be deleted)
         */
        [$matchedProductVariations, $unMatchedProductVariations] = $existingProductVariations->partition(function ($existingProductVariation) use (&$productVariationTemplates) {

            /**
             *  Get the name and value for the existing variation
             *
             *  $result1 = ["Sizes", "Large"]
             *
             */
            $result1 = $existingProductVariation->variables->mapWithKeys(function($variable){
                return [$variable->name => $variable->value];
            });

            /**
             *  If the variation exists then move it to the $matchedProductVariations,
             *  but If it does not exist then move it to the $unMatchedProductVariations
             */
            return collect($productVariationTemplates)->contains(function($productVariationTemplate, $key) use ($result1, &$productVariationTemplates) {

                /**
                 *  Get the name and value for the new variation template
                 *
                 *  $result2 = ["Sizes", "Large"]
                 *
                 */
                $result2 = collect($productVariationTemplate['variableTemplates'])->mapWithKeys(function($variable){
                    return [$variable['name'] => $variable['value']];
                });

                /**
                 *  If the following checks pass
                 *
                 *  (1) There is no difference between the set of result1 vs result2
                 *  (2) There is no difference between the set of result2 vs result1
                 *
                 *  Then we can easily assume that this $productVariationTemplate
                 *  already exists as $existingProductVariation and must be
                 *  excluded from the list of new variations to create.
                 *
                 */
                $exists = $result1->diffAssoc($result2)->count() == 0 &&
                          $result2->diffAssoc($result1)->count() == 0;

                //  If the variation does exist
                if( $exists === true ) {

                    //  Then we must remove the assosiated $productVariationTemplate
                    $productVariationTemplates->forget($key);

                }

                return $exists;

            });

        });

        //  If we have existing variations that have no match
        if( $unMatchedProductVariations->count() ) {

            //  Delete each variation
            $unMatchedProductVariations->each(fn($unMatchedProductVariation) => $unMatchedProductVariation->delete());

        }

        //  If we have new variations
        if($productVariationTemplates->count()) {

            //  Create the new product variations
            Product::insert(

                //  Extract only the Product fillable fields
                $productVariationTemplates->map(
                    fn($productVariationTemplate) => collect($productVariationTemplate)->only(
                        resolve(Product::class)->getFillable()
                    )
                )->toArray()

            );

            //  Get the updated product variations
            $existingProductVariations = $this->model->variations()->get();

            //  Update the product variable templates
            $variableTemplates = $existingProductVariations->flatMap(function($existingProductVariation) use ($productVariationTemplates) {

                /**
                 *  Search the product variation template whose name matches this newly created product variation.
                 *  After finding this match, extract the "variableTemplates" from the "productVariationTemplate"
                 *
                 *  $variableTemplates = [
                 *      [
                 *          "name": "Colors",
                 *          "value": "Red"
                 *      ],
                 *      [
                 *          "name": "Sizes",
                 *          "value": "Large"
                 *      ]
                 *  ];
                 */
                $productVariationTemplate = $productVariationTemplates->first(function($productVariationTemplate) use ($existingProductVariation) {

                    //  The names must match
                    return $existingProductVariation->name === $productVariationTemplate['name'];

                });

                //  If we have a matching $productVariationTemplate
                if( $productVariationTemplate ) {

                    //  Get the $variableTemplates
                    $variableTemplates = $productVariationTemplate['variableTemplates'];

                    /**
                     *  Set the product id that this variable template must relate to
                     *
                     *  $variableTemplates = [
                     *      [
                     *          "name": "Colors",
                     *          "value": "Red",
                     *          "product_id": 2
                     *      ],
                     *      [
                     *          "name": "Sizes",
                     *          "value": "Large",
                     *          "product_id": 2
                     *      ]
                     *  ];
                     */
                    return collect($variableTemplates)->map(function($variableTemplate) use ($existingProductVariation) {

                        //  Set the parent product id
                        $variableTemplate['product_id'] = $existingProductVariation->id;

                        return $variableTemplate;

                    });

                }

                //  Incase we don't have a match, return an empty array
                return [];

            });

            //  Create the new variables
            Variable::insert($variableTemplates->toArray());

            //  Count all the product variations
            $totalVariations = $this->model->variations()->count();

            //  Count all the visible product variations
            $totalVisibleVariations = $this->model->variations()->visible()->count();

            //  Update the product
            $this->model->update([
                'allow_variations' => true,
                'total_variations' => $totalVariations,
                'variant_attributes' => $variantAttributes,
                'total_visible_variations' => $totalVisibleVariations,
            ]);

        }

        return $this->showVariations($request);

    }
}
