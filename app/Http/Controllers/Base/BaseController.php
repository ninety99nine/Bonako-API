<?php

namespace App\Http\Controllers\Base;

use Illuminate\Support\Str;
use Illuminate\Http\Response;
use App\Repositories\BaseRepository;
use App\Repositories\SubscriptionRepository;
use Illuminate\Database\Eloquent\Model;

class BaseController extends Controller
{
    protected $repository;
    protected $repositoryClass;
    protected $repositoryClassName;
    protected $repositoryClassPath = 'App\Repositories\\';

    public function __construct()
    {
        /**
         *  In previous versions of Laravel, you could access session variables or the
         *  authenticated user in your controller's constructor. In current releases
         *  of Laravel the constructor's construct method is resolved before any
         *  middleware is run, which means that anything that is resolved on the
         *  constructor's construct method by dependency injection does not have
         *  access to middleware resolved values e.g auth()->user() would return
         *  null since the user has not been set yet. This means that even if
         *  we can resolve these classes, if won't have access to important
         *  values set by middlewares.
         *
         *  Our repository classes such as UserRepository, StoreRepository,
         *  OrderRepository and more require access to the authenticated
         *  user, therefore must be resolveD after the auth middleware
         *  has been executed
         *
         *  To fix this, we can resolve the classes inside a middleware
         *  then set the value on the controller's public property.
         *
         *  Refer to: https://laravel.com/docs/5.3/upgrade#5.3-session-in-constructors
         */
        $this->middleware(function ($request, $next) {

            /**
             *  $this->repositoryClassName can be set manually if the repository class
             *  has a unique name that we cannot implicitly derive. If the value
             *  of $this->repositoryClassName is NULL then we can implicitly derive
             *  the class name.
             */
            if( $this->repositoryClassName === null ) {

                /**
                 *  Implicitly derive the repository class name in the following way:
                 *
                 *  If the sub-class name is "StoreController", then replace the
                 *  word "Controller" with "Repository" and set the result as
                 *  the repository class name to resolve. In this case the
                 *  final result must be "StoreRepository".
                 */
                $this->repositoryClassName = Str::replace('Controller', 'Repository', class_basename($this));

            }

            //  Set the repository class
            $this->repositoryClass = $this->repositoryClassPath . $this->repositoryClassName;

            //  Resolve and set the repository result
            $this->repository = resolve($this->repositoryClass);

            //  Continue the request
            return $next($request);

        });
    }

    public function prepareOutput($output, $status = Response::HTTP_OK) {

        //  Check if the output is an instance of the BaseRepository
        if( $output instanceof BaseRepository) {

            //  Transform and return the output
            $output = $output->transform();

        }

        // Return the output with the response status code
        return response($output, $status);

    }

    public function setModel(Model $model) {
        return $this->repository->setModel($model);
    }
}
