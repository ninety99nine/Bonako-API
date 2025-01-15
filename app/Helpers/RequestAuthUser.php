<?php

namespace App\Helpers;

use App\Models\User;
use App\Enums\CacheName;
use App\Helpers\CacheManager;

class RequestAuthUser
{
    public $authUser;

    /**
     *  @param User|null $authUser
     */
    public function __construct($authUser = null)
    {
        $this->authUser = $authUser;
    }

    /**
     *  Set the authenticated user on the cache.
     *  ----------------------------------------
     *  Sometimes the access token is provided, such as when the access token
     *  was created by the getUserAndAccessToken() of the AuthRepository. We
     *  can pass this same access token as a parameter so that we do not have
     *  to waste a database query to get the same access token. We can check
     *  if the $accessToken is provided, otherwise then we can query the
     *  access token using:
     *
     *  $authUser->currentAccessToken()
     *
     *  @return RequestAuthUser
     */
    public function setAuthUserOnRequest()
    {
        if(!$this->authUser) {

            // If the bearer token is provided
            if ($bearerToken = request()->bearerToken()) {

                //  Get the authenticated user from the cache
                $this->authUser = $this->cacheManagerUsingBearerToken($bearerToken)->get();

                //  If the authenticated user does not exist on the cache
                if(!$this->authUser) {

                    //  Query the authenticated user (if user exists / token is valid)
                    $this->authUser = auth()->user();

                    //  If the authenticated user was found (i.e token was valid)
                    if($this->authUser) {

                        //  Set the authenticated user on the cache
                        $this->setAuthUserOnCache($bearerToken);

                    }

                }

            }

        }

        //  If the autheticated user is available but not set
        if($this->authUser && !auth()->hasUser()) {

            //  Set the autheticated user
            auth()->setUser($this->authUser);

        }

        //  Get the status of the authenticated user's existence
        $authUserExists = $this->authUser != null;

        //  Merge the authUserExists and authUser into the request
        request()->merge([
            'auth_user_exists' => $authUserExists,
            'auth_user' => $this->authUser
        ]);

        return $this;
    }

    /**
     *  Set the authenticated user on the cache.
     *  ----------------------------------------
     *  Sometimes the access token is provided, such as when the access token
     *  was created by the getUserAndAccessToken() of the AuthRepository. We
     *  can pass this same access token as a parameter so that we do not have
     *  to waste a database query to get the same access token. We can check
     *  if the $accessToken is provided, otherwise then we can query the
     *  access token using:
     *
     *  $authUser->currentAccessToken()
     *
     *  @param string|null $bearerToken
     *  @param \Laravel\Sanctum\Contracts\HasAbilities|null $accessToken
     *  @return RequestAuthUser
     */
    public function setAuthUserOnCache($bearerToken = null, $accessToken = null)
    {
        //  Get the current bearer token for the authenticated user
        $bearerToken = $accessToken ?? request()->bearerToken();

        //  Get the current access token for the authenticated user
        $accessToken = $accessToken ?? $this->authUser->currentAccessToken();

        //  If the token does not have an expiry date and time
        if(is_null($accessToken->expires_at)) {

            $remainingValidity = null;

        }else{

            // Get the expiration date of the access token
            $tokenExpiration = $accessToken->expires_at->getTimestamp();

            // Calculate the remaining validity period of the token in seconds
            $remainingValidity = max(0, $tokenExpiration - now()->timestamp);

        }

        //  Set the cache expiry time as the maximum threshold (600 seconds = 10 minutes) - Default
        $cacheExpiryDatetime = 600;

        //  If the token has a remaining validity
        if(!is_null($remainingValidity)) {

            // Determine the cache expiry time as the minimum of remaining validity and maximum threshold
            $cacheExpiryDatetime = now()->addSeconds(min($remainingValidity, $cacheExpiryDatetime));

        }

        //  Cache the authenticated user with their API token as the cache key (Cache for 10 minutes)
        $this->cacheManagerUsingBearerToken($bearerToken)->put($this->authUser, $cacheExpiryDatetime);

        /**
         *  Cache the authenticated user's bearer token with their access token id as the cache key (Cache for 10 minutes)
         *  This is useful since we would need to capture the original bearer token whenever the removeTokensFromDatabaseAndCache()
         *  method of the AuthRepository is called. It will fetch the Access Token from the database and then use the id of that
         *  access token to find the bearer token in the cache so that we can then use that bearer token to remove the
         *  authenticated user from the cache. Remember that deleting the access token from the database if not enough,
         *  since its possible that a user may still remain logged in as long as that user remains cached on that
         *  same bearer token. We need to make sure that we have both deleted the access token and forgotten the
         *  user from the cache. Forgetting the user from the cache requires that we know the bearer token of
         *  that user. In cases when we want to perform a logout all devices, we may only know the bearer
         *  token of the current request, but not the bearer tokens of the other devices by the same
         *  user. This is why we need to also cache the bearer tokens against their associated
         *  access tokens.
         */
        $this->cacheManagerUsingAccessToken($accessToken)->put($bearerToken, $cacheExpiryDatetime);

        return $this;
    }

    /**
     *  Forget the authenticated user from the cache matching the specified access token
     *
     *  @return bool
     */
    public function forgetAuthUserOnCacheUsingAccessToken($accessToken)
    {
        $cacheManagerUsingAccessToken = $this->cacheManagerUsingAccessToken($accessToken);

        //  Check if the bearer token for this access token exists on the cache
        if( $cacheManagerUsingAccessToken->has() ) {

            //  Get the bearer token for this access token from the cache
            $bearerToken = $cacheManagerUsingAccessToken->get();

            //  Forget the authenticated user matching this bearer token
            $this->cacheManagerUsingBearerToken($bearerToken)->forget();

            //  Forget this bearer token
            $cacheManagerUsingAccessToken->forget();

        }

        return true;
    }

    /**
     *  Get the cache manager for specified bearer token
     *
     *  @return CacheManager
     */
    public function cacheManagerUsingBearerToken($bearerToken)
    {
        return (new CacheManager(CacheName::AUTH_USER_ON_REQUEST))->append($bearerToken);
    }

    /**
     *  Get the cache manager for specified access token
     *
     *  @return CacheManager
     */
    public function cacheManagerUsingAccessToken($accessToken)
    {
        return (new CacheManager(CacheName::AUTH_USER_ON_REQUEST_BEARER_TOKEN))->append($accessToken->id);
    }
}
