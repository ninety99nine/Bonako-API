<?php

namespace App\Repositories;

use App\Enums\CacheName;
use App\Enums\RefreshModel;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\CacheManager;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\InvalidPerPageException;
use App\Services\Sorting\RepositorySorting;
use App\Services\Filtering\RepositoryFilter;
use App\Exceptions\InvalidPaginateException;
use Illuminate\Validation\ValidationException;
use App\Exceptions\DeleteConfirmationCodeInvalid;
use App\Exceptions\RepositoryQueryFailedException;
use App\Exceptions\RepositoryModelNotFoundException;
use App\Models\User;

abstract class BaseRepository
{
    protected $model;
    protected $formRequest;
    protected $paginate = true;
    protected $transform = true;
    protected $collection = null;
    protected $createIgnoreFields = [];
    protected $updateIgnoreFields = [];
    protected $requiresConfirmationBeforeDelete = false;

    //  Limit the total results to 15 items by default
    protected $perPage = 15;

    /**
     *  Overide this repository Eloquent class name.
     *  This represents the Model name to use
     *  whenever we want to refer to this Model
     *  plainly. This name is also used to
     *  describe exception messages.
     */
    protected $modelName;

    /**
     *  Overide this repository Eloquent Model class name.
     *  This represents the Model to target for this
     *  repository instance.  If this is not provided
     *  we will implicity define the class name
     */
    protected $modelClass;

    /**
     *  Overide this repository Resource class name.
     *  This represents the Resource used to transform
     *  the Model repository instance.  If this is not
     *  provided we will implicity define the class
     *  name
     */
    protected $resourceClass;

    /**
     *  Overide this repository Resources class name.
     *  This represents the Resource used to transform
     *  a collection of Model repository instances. If
     *  this is not provided we will implicity define
     *  the class name
     */
    protected $resourceCollectionClass;

    /**
     *  First thing is first, we need to set the Eloquent Model Instance of
     *  the target model class so that we can use the repository methods
     */
    public function __construct()
    {
        $this->setModel();
    }


    /**
     *  Return the Model Class instance
     */
    public function getModel($model = null) {

        return $this->model ?? $model;

    }

    /**
     *  Set the model by resolving the provided Eloquent
     *  class name from the service container e.g
     *
     *  $this->model = resolve(App\Models\Store)
     *
     *  This means that our property "$this->model" is
     *  now an Eloquent Model Instance of Store.
     *
     *  Sometimes we can just pass our own specific model
     *  instance by passing it as a parameter e.g passing
     *  a "Store" model with id "1"
     *
     *  e.g $model = Store::find(1)
     *
     *  Or we can pass a model Eloquent Builder
     *
     *  e.g $model = User::find(1)->stores()
     *
     *  This is helpful to set an Eloquent Builder instance
     *  then chain the get() method to pull the query results.
     */
    public function setModel($model = null) {

        if( ($model !== null) || ($this->model === null) ) {

            $this->model = $model ? $model : resolve($this->getModelClass());

        }

        /**
         *  Return the Repository Class instance. This is so that we can chain other
         *  methods if necessary
         */
        return $this;
    }

    private function getModelClass() {
        if( $this->modelClass === null ) {
            return $this->getFallbackModelClass();
        }
        return $this->getProvidedModelClass();
    }

    private function getProvidedModelClass() {
        /**
         *  Get the sub-class Eloquent Model class name, for instance,
         *  $this->resourceClass = Store::class"
         */
        return $this->modelClass;
    }

    private function getFallbackModelClass() {
        /**
         *  If the sub-class name is "StoreRepository", then replace the
         *  word "Repository" with nothing and append the class path.
         *
         *  Return a fully qualified class path e.g App\Models\Store
         */
        return 'App\Models\\' . Str::replace('Repository', '', class_basename($this));
    }

    private function getResourceClass() {
        if( $this->resourceClass === null ) {
            return $this->getFallbackResourceClass();
        }
        return $this->getProvidedResourceClass();
    }

    private function getProvidedResourceClass() {
        /**
         *  Get the sub-class Resource class name, for instance,
         *  $this->resourceClass = Store::class"
         */
        return $this->resourceClass;
    }

    private function getFallbackResourceClass() {
        /**
         *  If the sub-class name is "StoreRepository", then replace the
         *  word "Repository" with "Resource" and append the class path.
         *
         *  Return a fully qualified class path e.g App\Http\Resources\StoreResource
         */
        return '\App\Http\Resources\\' . Str::replace('Repository', 'Resource', class_basename($this));
    }



    private function getResourceCollectionClass() {
        if( $this->resourceCollectionClass === null ) {
            return $this->getFallbackResourceCollectionClass();
        }
        return $this->getProvidedResourceCollectionClass();
    }

    private function getProvidedResourceCollectionClass() {
        /**
         *  Get the sub-class Resource class name, for instance,
         *  $this->resourceCollectionClass = Store::class"
         */
        return $this->resourceCollectionClass;
    }

    private function getFallbackResourceCollectionClass() {
        /**
         *  If the sub-class name is "StoreRepository", then replace the
         *  word "Repository" with "Resources" and append the class path.
         *
         *  Return a fully qualified class path e.g App\Http\Resources\StoreResources
         */
        return '\App\Http\Resources\\' . Str::replace('Repository', 'Resources', class_basename($this));
    }




    private function getModelName() {
        if( $this->modelName === null ) {
            return $this->getFallbackModelName();
        }
        return $this->getProvidedModelName();
    }

    public function getModelNameInLowercase() {
        return strtolower($this->getModelName());
    }

    private function getProvidedModelName() {
        /**
         *  Get the provided model name e.g user, store, order
         *  Trim and lowercase the model name
         */
        return Str::of(strtolower($this->modelName))->trim();
    }

    private function getFallbackModelName() {
        /**
         *  If the sub-class name is "StoreRepository", then remove the
         *  word "Repository" from the class base name and assume
         *  the remaining characters to be the name of the
         *  Eloquent Model Name to target i.e "Store"
         */
        return Str::of(Str::replace('Repository', '', class_basename($this)))->trim();
    }

    private function handleSearch() {

        //  Get the search word
        $searchWord = request()->input('search');

        //  if we have a search word
        if( !empty( $searchWord ) ){

            //  Limit the model to the search scope
            $this->model = $this->model->search($searchWord);

        }

    }

    private function handleFilters() {

        //  Resolve and attempt to apply filters on this repository model instance
        $this->model = resolve(RepositoryFilter::class)->apply($this->model);

    }

    private function handleSorting() {

        //  Resolve and attempt to apply sorting on this repository model instance
        $this->model = resolve(RepositorySorting::class)->apply($this->model);

    }

    private function handlePaginationInput() {

        //  If we want to overide the pagination
        if( request()->has('paginate') ) {

            //  If the paginate value is not provided
            if( !request()->filled('paginate') ) throw new InvalidPaginateException();

            //  Check if the paginate value is true or false
            $canPaginate = in_array(request()->input('paginate'), [true, false, 'true', 'false'], true);

            //  If the paginate value is not true or false
            if( !$canPaginate ) throw new InvalidPaginateException();

            //  Set the overide paginate value
            $this->paginate = in_array(request()->input('paginate'), [true, 'true'], true) ? true : false;

        }

    }

    private function handleTransformation() {


    }

    private function handlePerPageInput() {

        //  If we want to overide the default total
        if( request()->has('per_page') ) {

            //  If the per page value is not provided
            if( !request()->filled('per_page') ) throw new InvalidPerPageException();

            //  If the per page value is not a valid number
            if( !is_numeric( request()->input('per_page') ) ) throw new InvalidPerPageException();

            //  If the per page value is 0 or less
            if( request()->input('per_page') <= 0 ) throw new InvalidPerPageException('The per page value must be greater than zero (0) in order to limit the results');

            //  If the per page value must not exceed 100
            if( request()->input('per_page') > 100 ) throw new InvalidPerPageException('The per page value must not exceed 100 in order to limit the results');

            //  Set the overide per page value
            $this->perPage = (int) request()->input('per_page');

        }

    }

    /**
     *  Find and set specific repository model instance.
     *  Return this instance if found.
     *  Return exception if not found.
     */
    public function find($id) {

        //  If we have the repository model instance
        if( $this->model = $this->model->where('id', $id)->first() ){

            /**
             *  Return the Repository Class instance. This is so that we can chain other
             *  methods e.g to Transform the Model for external consumption.
             */
            return $this;

        }else{

            //  Throw an Exception
            throw new RepositoryModelNotFoundException('This '.strtolower($this->getModelName(), ).' does not exist');

        }

    }

    /**
     *  Find and return specific repository model instance.
     *  Return exception if not found
     */
    public function findModel($id) {

        return $this->find($id)->model;

    }

    /**
     *  Find and return transformed repository model instance.
     *  Return exception if not found
     */
    public function findAndTranform($id) {

        return $this->find($id)->transform();

    }

    /**
     *  Retrieve a fresh instance of the model.
     *  Return this instance.
     */
    public function refreshModel() {

        $this->setModel( $this->model->fresh() );

        return $this;

    }

    /**
     *  Get repository model instances.
     */
    public function get() {

        try {

            //  Filter the model instance
            $output = $this->handleSearch();

            //  Filter the model instance
            //  $output = $this->handleFilters();

            //  Sort the model instance
            //$output = $this->handleSorting();

            //  Filter the model instance
            $output = $this->handleTransformation();

            //  Handle the url per page input
            $this->handlePerPageInput();

            //  Handle the url paginate input
            $this->handlePaginationInput();

            //  If we want to paginate the collection
            if( $this->paginate ) {

                //  Initialise a paginated collection
                $this->setCollection($this->model->paginate($this->perPage));

            }else{

                //  Initialise a collection
                $this->setCollection($this->model->take($this->perPage)->get());

            }

            /**
             *  Resolve caching Error:
             *
             *  $paymentMethods = PaymentMethod::orderBy('position', 'asc');
             *  $paymentMethodRepository = (new PaymentMethodRepository)->setModel($paymentMethods);
             *
             *  Cache::remember('PAYMENT_METHODS', now()->addDay(), function() {
             *
             *      $paymentMethods = PaymentMethod::orderBy('position', 'asc');
             *      return (new PaymentMethodRepository)->setModel($paymentMethods)->get();
             *
             *  });
             *
             *  Notice that for this PaymentMethodRepository, $this->model is equal to
             *  Query Builder Instance e.g:
             *
             *  $this->model = PaymentMethod::orderBy('position', 'asc');
             *
             *  When we try and cache the callback, we are going to get the following error:
             *
             *  Serialization of 'PDO' is not allowed
             *
             *  An Eloquent Query Builder Instance has a reference to a PDO instance since the PDO instance is used to build the queries.
             *  PDO objects contain active links to databases (which may have a transaction initiated or DB session settings and variables).
             *  You cannot serialize a PDO object because the above would get lost and cannot be re-established automatically. This means
             *  whenever we try to Cache any Repository where $this->model = Eloquent Query Builder Instance, we  will get this error.
             *
             *  To resolve this issue, we need to set $this->model to a non Query Builder instance e.g $this->model = (new PaymentMethod);
             *
             *  This way the caching will work just fine.
             */
            $this->setModel( resolve($this->getModelClass()) );

            return $this;

        //  If we failed to perform the query
        } catch (\Illuminate\Database\QueryException $e){

            report($e);

            throw $e;

            throw new RepositoryQueryFailedException('Could not get the '.$this->getModelNameInLowercase().' records because the Database Query failed. Make sure that your filters and sorting functionality is correctly set especially when targeting nested relationships');

        }

    }

    public function count()
    {
        return $this->model->count();
    }

    /**
     *  Set the repository collection
     *
     *  @param Illuminate\Database\Eloquent\Collection | Illuminate\Pagination\LengthAwarePaginator $collection
     *  @return BaseRepository
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     *  Create new repository model instance.
     *
     *  @param Request | Model | array $data The data that must be used to create the record
     */
    public function create($data, $refreshModel = RefreshModel::YES)
    {
        //  Get the extracted data
        $data = $this->extractDBData($data);

        //  Ignore unsupported fields
        $data = collect($data)->except($this->createIgnoreFields)->all();

        //  Save the model into the database
        $createdModel = $this->model->create($data);

        if( $refreshModel == RefreshModel::YES ) {

            //  Set a fresh instance of this created model
            $this->setModel( $createdModel->fresh() );

        }else{

            //  Set this created model
            $this->setModel( $createdModel );

        }

        /**
         *  Return the Repository Class instance. This is so that we can chain other
         *  methods e.g to Transform the Model for external consumption.
         */
        return $this;
    }

    /**
     *  Update an existing repository model instance.
     *  Set the model instance that must be updated
     *  before chaining this method e.g
     *
     *  $this->setModel($model)->update($data);
     *
     *  @param Request | Model | array $data The data that must be used to update the record
     */
    public function update($data)
    {
        //  Get the extracted data
        $data = $this->extractDBData($data);

        //  If we are updating a Model
        if($this->model instanceof Model) {

            $attributes = collect($this->model->getAttributes())->keys();

            foreach($data as $key => $value) {

                if($attributes->contains($key)) {

                    $this->model->setAttribute($key, $value);

                }
            }

            $canUpdate = $this->model->isDirty();

            // If we can update the repository model
            if ($canUpdate) {

                //  Update repository model
                $this->model->update($data);

                //  Set repository model
                $this->setModel( $this->model->fresh() );

            }

        //  If we are updating a non Model instance (HasMany, belongsToMany, morphMany, e.t.c) e.g $store->shortcodes()
        }else{

            //  Update the non Model instance
            $this->model->update($data);

        }

        /**
         *  Return the Repository Class instance. This is so that we can chain other
         *  methods e.g to Transform the Model for external consumption.
         */
        return $this;
    }

    /**
     *  Return the data for creating or updating a model
     *
     *  @param Request | Model | array $data
     */
    public function extractDBData($data)
    {
        //  If the data is within a Request
        if(($request = $data) instanceof Request) {

            return $request->all();

        //  If the data is within a Model
        }elseif(($model = $data) instanceof Model) {

            return $model->getAttributes();

        //  If the data is an Array
        }elseif(is_array($data)) {

            return $data;

        }

        return [];

    }

    /**
     *  Show the available repository model cancellation reasons
     *
     *  @return BaseRepository
     */
    public function showCancellationReasons()
    {
        return $this->model::CANCELLATION_REASONS;
    }

    /**
     *  Cancel the repository model instance
     *
     *  @return BaseRepository
     */
    public function cancel(Request $request)
    {
        //  Set the cancellation reason (if available)
        $cancellationReason = $request->input('cancellation_reason') ?: null;

        //  Cancel the repository model instance
        return $this->update(['is_cancelled' => true, 'cancellation_reason' => $cancellationReason]);
    }

    /**
     *  Uncancel the repository model instance
     *
     *  @return BaseRepository
     */
    public function uncancel()
    {
        //  Uncancel the order
        return  $this->update(['is_cancelled' => false, 'cancellation_reason' => null]);
    }

    /**
     *  Delete existing repository model instance.
     *
     *  @param bool $forceDelete - Whether to permanently delete this resource
     *  @return array
     */
    public function delete($forceDelete = false) {
        try {

            //  If this requires confirmation before delete
            if( $this->requiresConfirmationBeforeDelete ){

                //  Confirm that the user can delete this model
                $this->confirmDeleteConfirmationCode();

                //  Remove the code
                $this->removeDeleteConfirmationCode();

            }

            if( $forceDelete ) {

                $this->model->forceDelete();

            }else{

                $this->model->delete();

            }

            return [
                'message' => 'Deleted successfully'
            ];

        } catch (RepositoryModelNotFoundException $e) {

            throw new RepositoryModelNotFoundException('Could not delete '.$this->getModelNameInLowercase().' because it does not exist.');

        } catch (\Throwable $th) {

            throw $th;

        }
    }

    /**
     *  Generate the code required to delete important assets
     *
     *  @return array
     */
    public function generateDeleteConfirmationCode()
    {
        //  Generate random 6 digit number
        $code = $this->generateRandomSixDigitCode();

        //  Cache the new code for exactly 10 minutes
        $this->getDeleteConfirmationCodeCacheManager()->put($code, now()->addMinutes(10));

        return [
            'message' => 'Enter the confirmation code "'.$code.'" to confirm deleting this ' . $this->getModelNameInLowercase(),
            'code' => $code
        ];
    }

    /**
     *  Confirm the code required to delete important assets
     */
    public function confirmDeleteConfirmationCode()
    {
        $code = request()->input('code');

        //  If the code is provided
        if( !empty($code) ) {

            //  Check if the confirmation code is a string
            if( !is_string($code) ) {

                //  Throw an Exception - Confirmation code must be a string
                throw ValidationException::withMessages(['code' => 'The confirmation code must be a string']);

            }

            if( $this->getDeleteConfirmationCodeCacheManager()->has() ) {

                if( $code == $this->getDeleteConfirmationCodeCacheManager()->get() ) {

                    return true;

                }else{

                    throw new DeleteConfirmationCodeInvalid('The confirmation code "'.$code.'" is invalid.');

                }

            }else{

                throw new DeleteConfirmationCodeInvalid('The confirmation code "'.$code.'" has expired.');

            }

        }else{

            //  Throw an Exception - Confirmation code required
            throw ValidationException::withMessages(['code' => 'The confirmation code is required']);

        }

    }

    /**
     *  Remove the code required to delete important assets
     */
    public function removeDeleteConfirmationCode()
    {
        $this->getDeleteConfirmationCodeCacheManager()->forget();
    }

    /**
     *  Get the confirmation code cache manager
     *
     *  @return CacheManager
     */
    public function getDeleteConfirmationCodeCacheManager()
    {
        return (new CacheManager(CacheName::DELETE_CONFIRMATION_CODE))->appendModel($this->model);
    }

    /**
     *  Generate a random 6 digit number
     */
    public function generateRandomSixDigitCode()
    {
        return rand(100000, 999999);
    }

    /**
     *  Transform the repository model instance.
     */
    public function transform() {

        //  If we are transforming a collection of items
        if( !is_null($this->collection) ) {

            /**
             *  The resource parameter must be set to the current collection instance
             *  so that we can transform the collection data for external consumption.
             */

            $class = $this->getResourceCollectionClass();
            $params = ['resource' => $this->collection];

        //  If we are transforming a single item
        }else{

            /**
             *  The resource parameter must be set to the current model instance
             *  so that we can transform the model data for external consumption.
             */
            $class = $this->getResourceClass();
            $params = ['resource' => $this->model];

        }

        return resolve($class, $params);

    }

}
