<?php

namespace App\Http\Resources;

use Illuminate\Support\Str;
use App\Models\Base\BasePivot;
use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\Helpers\ResourceLink;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{
    /**
     *  Check if this user is a Super Admin
     */
    protected $isSuperAdmin;

    /**
     *  Check if this user is an Authourized User
     */
    protected $isAuthourizedUser;

    /**
     *  Check if this user is an Public User
     */
    protected $isPublicUser;

    /**
     *  Fields to overide the default provided fillable fields of the model
     */
    protected $customFields = null;

    /**
     *  Fields to add to be part of the final transformed fields
     */
    protected $customIncludeFields = null;

    /**
     *  Fields to remove from being part of the final transformed fields
     */
    protected $customExcludeFields = null;

    /**
     *  Attributes to overide the default provided appends of the model
     */
    protected $customAttributes = null;

    /**
     *  Attributes to add to be part of the final transformed attributes
     */
    protected $customIncludeAttributes = null;

    /**
     *  Attributes to remove from being part of the final transformed attributes
     */
    protected $customExcludeAttributes = null;

    /**
     *  Relationships to overide the default provided relationships of the model
     */
    protected $customRelationships = null;

    /**
     *  Relationships to add to be part of the final relationships
     */
    protected $customIncludeRelationships = null;

    /**
     *  Relationships to remove from being part of the final relationships
     */
    protected $customExcludeRelationships = null;

    /**
     *  Whether to show the attributes payload
     */
    protected $showAttributes = true;

    /**
     *  The resource links
     */
    protected $resourceLinks = [];

    /**
     *  Whether to show the links payload
     */
    protected $showLinks = true;

    /**
     *  The resource relationships
     */
    protected $resourceRelationships = [];

    /**
     *  Whether to show the relationships payload
     */
    protected $showRelationships = true;

    public function __construct($resource)
    {
        /**
         *  Run the normal Laravel JsonResource __construct() method
         *  so that Laravel can do the usual procedure before we
         *  run our additional logic.
         */
        parent::__construct($resource);

        /**
         *  If this action is performed by a user to their own profile,
         *  then this is considered an authourized user
         *
         *  The following is checking if this action was performed
         *  under the route names:
         *
         *  "auth.login", "auth.register", "auth.login", "auth.reset.password", e.t.c
         */
        $this->isAuthourizedUser = request()->routeIs('auth.*');
        $this->isSuperAdmin = ($user = request()->user()) ? $user->isSuperAdmin() : false;
        $this->isPublicUser = !($this->isAuthourizedUser || $this->isSuperAdmin);

        //  If the request does not intend to disable casting completely
        if( !in_array(request()->input('_noCasting'), [true, 'true', '1'], true) ) {

            /**
             *  Some Models such as the Laravel Based Models e.g Illuminate\Notifications\DatabaseNotification
             *  which is the Laravel Notification Model do not implement our custom getTranformableCasts(),
             *  therefore we need to check whether or not this resource contains this method before we
             *  attempt to call it.
             */
            if( method_exists($this->resource, 'getTranformableCasts') ) {

                /**
                 *  Cast the fields of this resource
                 *
                 *  Apply the temporary cast at runtime using the mergeCasts method.
                 *  These cast definitions will be added to any of the casts already
                 *  defined on the model
                 *
                 *  Refer to: https://laravel.com/docs/8.x/eloquent-mutators
                 */
                $this->mergeCasts($this->getTranformableCasts());

            }

        }
    }

    /**
     *  Transform the resource into an array.
     */
    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    /**
     *  Transform the resource into an array from transformable
     *  fields and attributes of the model instance
     */
    public function transformedStructure()
    {
        //  If the request does not intend to hide the fields completely
        if( !in_array(request()->input('_noFields'), [true, 'true', '1'], true) ) {

            //  Set the transformable model fields
            $data = $this->extractFields();

        }else{

            $data = [];

        }

        //  If the request does not intend to hide the attributes completely
        if( !in_array(request()->input('_noAttributes'), [true, 'true', '1'], true) ) {

            //  Set the transformable model attributes (If permitted)
            $data['_attributes'] = $this->showAttributes ? $this->extractAttributes() : [];

        }

        //  If the request does not intend to hide the links completely
        if( !in_array(request()->input('_noLinks'), [true, 'true', '1'], true) ) {

            //  Set the transformable model links (If permitted)
            $data['_links'] = $this->showLinks ? $this->getLinks() : [];

        }

        //  If the request does not intend to hide the relationships completely
        if( !in_array(request()->input('_noRelationships'), [true, 'true', '1'], true) ) {

            //  Set the transformable model relationships (If permitted)
            $data['_relationships'] = $this->showRelationships ? $this->getRelationships() : [];

        }

        return $data;
    }

    /**
     *  Return the transformable model fields
     */
    public function extractFields()
    {
        //  Get model fields (Method defined in the App\Models\Traits\BaseTrait)
        $fields = $this->customFields === null ? collect($this->getAttributes())->keys()->toArray() : $this->customFields;

        //  Include additional custom fields
        $fields = $this->customIncludeFields === null ? $fields : collect($fields)->merge($this->customIncludeFields)->toArray();

        //  Exclude additional custom fields
        $fields = $this->customExcludeFields === null ? $fields : collect($fields)->filter(fn($field) => !in_array($field, $this->customExcludeFields))->toArray();

        //  If the request intends to specify specific fields to show
        if( request()->filled('_includeFields') == true ) {

            //  Capture the fields that we must include
            $fieldsToInclude = Str::of( request()->input('_includeFields') )->explode(',')->map(function($field) {

                return Str::replace(' ', '', Str::snake($field));

            })->toArray();

            $fields = collect($fields)->filter(fn($field) => in_array($field, $fieldsToInclude))->toArray();

        //  If the request intends to specify specific fields to exclude
        }elseif( request()->filled('_excludeFields') == true ) {

            //  Capture the fields that we must exclude
            $fieldsToExclude = Str::of( request()->input('_excludeFields') )->explode(',')->map(function($field) {

                return Str::replace(' ', '', Str::snake($field));

            })->toArray();

            $fields = collect($fields)->filter(fn($field) => !in_array($field, $fieldsToExclude))->toArray();

        }

        //  Return the fields as key-value pairs
        return collect($fields)->map(fn($field) => [$field => $this->$field])->collapse();
    }

    /**
     *  Return the transformable model attributes
     */
    public function extractAttributes()
    {
        /**
         *  Some Models such as the Laravel Based Models e.g Illuminate\Notifications\DatabaseNotification
         *  which is the Laravel Notification Model do not implement our custom getTransformableAppends(),
         *  therefore we need to check whether or not this resource contains this method before we
         *  attempt to call it.
         */
        if( $this->customAttributes === null && method_exists($this->resource, 'getTransformableAppends') ) {

            //  Get model attributes (Method defined in the App\Models\Traits\BaseTrait)
            $attributes = $this->getTransformableAppends();

        }else if($this->customAttributes !== null) {

            //  Get model attributes (Method defined by this Resource Class)
            $attributes = $this->customAttributes;

        }else{

            $attributes = [];

        }

        //  Include additional custom attributes
        $attributes = $this->customIncludeAttributes === null ? $attributes : collect($attributes)->merge($this->customIncludeAttributes)->toArray();

        //  Exclude additional custom attributes
        $attributes = $this->customExcludeAttributes === null ? $attributes : collect($attributes)->filter(fn($field) => !in_array($field, $this->customExcludeAttributes))->toArray();

        //  If the request intends to specify specific attributes to show
        if( request()->filled('_includeAttributes') == true ) {

            //  Capture the attributes that we must include
            $attributesToInclude = Str::of( request()->input('_includeAttributes') )->explode(',')->map(function($attribute) {

                return Str::replace(' ', '', Str::snake($attribute));

            })->toArray();

            $attributes = collect($attributes)->filter(fn($attribute) => in_array($attribute, $attributesToInclude))->toArray();

        //  If the request intends to specify specific attributes to exclude
        }elseif( request()->filled('_excludeAttributes') == true ) {

            //  Capture the attributes that we must exclude
            $attributesToExclude = Str::of( request()->input('_excludeAttributes') )->explode(',')->map(function($attribute) {

                return Str::replace(' ', '', Str::snake($attribute));

            })->toArray();

            $attributes = collect($attributes)->filter(fn($attribute) => !in_array($attribute, $attributesToExclude))->toArray();

        }

        //  Return the attributes as key-value pairs
        $attributes = collect($attributes)->map(fn($attribute) => [$attribute => $this->$attribute])->collapse();

        //  Foreach attribute
        foreach($attributes as $attribute) {

            //  If the attribute represents a Model as Pivot
            if($attribute instanceof BasePivot) {

                //  If the request does not intend to disable casting completely
                if( !in_array(request()->input('_noCasting'), [true, 'true', '1'], true) ) {

                    /**
                     *  Cast the fields of this resource
                     *
                     *  Apply the temporary cast at runtime using the mergeCasts method.
                     *  These cast definitions will be added to any of the casts already
                     *  defined on the model
                     *
                     *  Refer to: https://laravel.com/docs/8.x/eloquent-mutators
                     */
                    $attribute->mergeCasts($attribute->getTranformableCasts());

                }

            }

        }

        //  Return the attributes as key-value pairs
        return $attributes;
    }

    /**
     *  Overide to provide the links
     */
    public function setLinks()
    {
        $this->resourceLinks = [];
    }

    /**
     *  Return the transformable model links
     */
    public function getLinks()
    {
        $this->setLinks();

        //  If the request intends to specify specific links to show
        if( request()->filled('_includeLinks') == true ) {

            //  Capture the links that we must include
            $linksToInclude = Str::of( strtolower( Str::replace(' ', '', request()->input('_includeLinks') ) ) )->explode(',')->toArray();

            $this->resourceLinks = collect($this->resourceLinks)->filter(function($link) use ($linksToInclude) {

                /**
                 *  Note that since the link name can be represented in dot notation we need to check different cases
                 */
                return in_array(str_replace('.', '', $link->name), $linksToInclude);

            })->toArray();

        //  If the request intends to specify specific links to exclude
        }elseif( request()->filled('_excludeLinks') == true ) {

            //  Capture the links that we must exclude
            $linksToExclude = Str::of( strtolower( Str::replace(' ', '', request()->input('_excludeLinks') ) ) )->explode(',')->toArray();

            $this->resourceLinks = collect($this->resourceLinks)->filter(function($link) use ($linksToExclude) {

                /**
                 *  Note that since the link name can be represented in dot notation we need to check different cases
                 */
                return in_array(str_replace('.', '', $link->name), $linksToExclude);

            })->toArray();

        }

        return collect($this->resourceLinks)->map(fn(ResourceLink $resourceLink) => $resourceLink->getLink())->collapse();
    }

    /**
     *  Return the transformable model relationships
     */
    public function getRelationships()
    {
        /**
         *  Return the model relationships that are permitted for sharing.
         *  Foreach relationship available on the current model resource,
         *  we must check if that relationship is permitted for sharing.
         *
         *  The $relationship may be a single Eloquent Model or it may
         *  be an Eloquent Collection. The $relationshipName is the
         *  name of that relationship e.g orders, carts, products,
         *  e.t.c
         *
         *  Each relationship must be transformed according to its
         *  corresponding model repository class
         */

        if($this->customRelationships !== null) {
            $this->resourceRelationships = collect($this->resourceRelationships)->only($this->customRelationships);
        }

        if($this->customIncludeRelationships !== null) {
            $this->resourceRelationships = collect($this->resourceRelationships)->merge($this->customIncludeRelationships);
        }

        if($this->customExcludeRelationships !== null) {
            $this->resourceRelationships = collect($this->resourceRelationships)->except($this->customExcludeRelationships);
        }

        $relationships = collect($this->resource->getRelations())->keys()->filter(function($relationshipName) {

            return collect($this->resourceRelationships)->keys()->contains($relationshipName);

        })->mapWithKeys(function($relationshipName) {

            $transformedRelationship = null;

            //  Foreach of the permitted resource relationships
            foreach($this->resourceRelationships as $resourceRelationshipName => $repositoryClassName) {

                //  Check if the current relationship name matches the current permitted relationship name
                if( $relationshipName == $resourceRelationshipName ) {

                    $relationship = $this->resource->$relationshipName;

                    //  If the nested relationship is a single Model
                    if($relationship instanceof Model) {

                        //  Transform the single Model
                        $transformedRelationship = resolve($repositoryClassName)->setModel($relationship)->transform();

                    //  If the nested relationship is a Collection or Array of single Models
                    }else if($relationship instanceof Collection || is_array($relationship)) {

                        //  If the relationship is an Array, convert to a Collection
                        if( is_array($relationship) ) $relationship = collect($relationship);

                        //  Transform the Collection of Models
                        $transformedRelationship = resolve($repositoryClassName)->setCollection($relationship)->transform();

                    }
                }

            }

            return [$relationshipName => $transformedRelationship];

        });

        //  Return the relationships
        return $relationships;

    }
}
