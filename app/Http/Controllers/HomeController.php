<?php

namespace App\Http\Controllers;

use App\Enums\CacheName;
use Illuminate\Http\Request;
use App\Helpers\CacheManager;
use Illuminate\Support\Facades\DB;
use App\Services\Ussd\UssdService;
use App\Http\Resources\UserResource;
use App\Http\Resources\HomeResource;
use App\Repositories\UserRepository;
use App\Http\Controllers\Base\Controller;
use App\Repositories\StoreRepository;
use App\Services\MobileNumber\MobileNumberService;

class HomeController extends Controller
{
    public function apiHome(Request $request)
    {
        /**
         *  Since the api home endpoint does not require an authenticated user,
         *  i.e withoutMiddleware('auth:sanctum'), this means that the endpoint
         *  is open to everyone with or without the bearer token. This means
         *  that we never check the bearer token to block access on the
         *  condition of an invalid bearer token.
         *
         *  Note that the "auth_user" and "auth_user_exists" are set by our custom
         *  CaptureAuthUserOnRequest middleware class to retrieve the current
         *  authenticated user (if exists)
         */
        $authUser = $request->auth_user;
        $authUserExists = $request->auth_user_exists;
        $authUserDoesNotExist = $authUserExists == false;
        $hasProvidedMobileNumber = request()->filled('mobile_number');

        /**
         *  Performance Measure:
         *
         *  In order to reduce the number of API requests e.g sending one request to get this apiHome()
         *  response and then another request to check if the user account exists if the authenticated
         *  user was not found, we can allow for an automatic check of account existence when the
         *  authentication fails, given that the mobile number is provided.
         */
        if($authUserDoesNotExist && $hasProvidedMobileNumber) {

            //  Get the mobile if provided (Normally provided on X-Platform = USSD)
            $mobileNumber = request()->input('mobile_number');

            //  Cache manager
            $accountExists = (new CacheManager(CacheName::ACCOUNT_EXISTS))->append($mobileNumber)->remember(now()->addMinutes(10), function() use ($mobileNumber) {

                //  Add the mobile number extension
                $mobileNumber = MobileNumberService::addMobileNumberExtension($mobileNumber);

                /**
                 *  Check if the account using the provided mobile number exists in the database.
                 *
                 *  We could use User::searchMobileNumber($mobileNumber)->exists();
                 *
                 *  However, Query Builder was prefered for performance reasons.
                 */
                return DB::table('users')->where('mobile_number', $mobileNumber)->exists();

            });

        }else{

            if($authUserExists) {

                //  Set true since we know the account exists
                $accountExists = true;

            }else{

                //  Set null since we cannot determine the status of this request
                $accountExists = null;

            }

        }

        $data = [
            'accepted_terms_and_conditions' => $authUserExists ? $authUser->accepted_terms_and_conditions : false,
            'mobile_verification_shortcode' => UssdService::getMobileVerificationShortcode(),
            'mobile_number_extension' => MobileNumberService::getMobileNumberExtension(),
            'reserved_shortcode_range' => UssdService::getReservedShortcodeRange(),
            'user' => $authUserExists ? new UserResource($authUser) : null,
            'account_exists' => $accountExists,
            'authenticated' => $authUserExists,
        ];

        //  If the user exists and the response must include the user's resource totals
        if( $authUserExists && $request->filled('_include_resource_totals') ) {

            //  Get the user's resource totals
            $data['resource_totals'] = array_merge(
                (new UserRepository())->setModel($authUser)->showResourceTotals(),
                (new StoreRepository())->showResourceTotals()
            );

            //  If the request has set the "_include_fields"
            if( $request->filled('_include_fields') ) {

                //  Add the resource totals to the request "_include_fields"
                request()->merge(['_include_fields' => $request->input('_include_fields') .',resource_totals']);

            }

        }

        return (new HomeResource($data));
    }
}
