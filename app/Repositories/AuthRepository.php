<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\User;
use App\Enums\AccessToken;
use App\Events\LoginSuccess;
use Illuminate\Http\Request;
use App\Traits\Base\BaseTrait;
use App\Models\MobileVerification;
use App\Services\Ussd\UssdService;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;
use Illuminate\Validation\ValidationException;
use App\Exceptions\ResetPasswordFailedException;
use App\Exceptions\UpdatePasswordFailedException;
use App\Exceptions\LogoutOfSuperAdminRestrictedException;
use App\Exceptions\AcceptingTermsAndConditionsFailedException;
use App\Exceptions\MobileVerificationCodeGenerationFailedException;

class AuthRepository extends BaseRepository
{
    use BaseTrait;

    protected $modelName = 'user';
    protected $modelClass = User::class;
    protected $resourceClass = UserResource::class;
    protected $requiresConfirmationBeforeDelete = true;

    public function __construct(Request $request)
    {
        //  Run the base constructor
        parent::__construct();

        //  If the user is authenticated
        if(auth()->check()) {

            //  Set the authenticated user as the model
            $this->setModel( $this->getAuthUser() );

        }
    }

    /**
     *  Return the current authenticated user
     *
     *  @return User - Current authenticated user
     */
    public function getAuthUser()
    {
        return auth()->user();
    }

    /**
     *  Login without credentials for Requests originating from the USSD platform
     *  otherwise login with the mobile number and password for every other
     *  platform and return the user account and access token,
     *
     *  or ...
     *
     *  Set a new password for the existing account and return
     *  the existing user account and access token.
     *
     *  @param Request $request
     *  @return array
     */
    public function login(Request $request)
    {
        //  Set matching user
        $this->setModel($this->getUserFromMobileNumber());

        //  If the request is coming from the Ussd server then we do not need to verify the password
        if( UssdService::verifyIfRequestFromUssdServer() ) {

            //  Return user account and access token
            return $this->getUserAndAccessToken();

        }else{

            //  Check if the user already has a password
            if( $this->model->password ) {

                //  Get request password
                $password = $request->input('password');

                //  Check if we have a matching password for the user account
                if( Hash::check($password, $this->model->password) ) {

                    //  Get user account and access token
                    $userAndAccessToken = $this->getUserAndAccessToken();

                    //  Notify other devices on login success
                    broadcast(new LoginSuccess($userAndAccessToken['access_token']))->toOthers();

                    //  Return user account and access token
                    return $userAndAccessToken;

                }else {

                    //  Throw an Exception - Password does not match
                    throw ValidationException::withMessages(['password' => 'The password provided is incorrect.']);

                }

            //  Otherwise the user must update their user account password
            }else{

                $this->updateAccountPassword($request);

                //  Return user account and access token
                return $this->getUserAndAccessToken();

            }

        }
    }

    /**
     *  Register a new user account and return the
     *  user account and access token or just the
     *  user account without the access token.
     *
     *  @param Request $request
     *  @param AccessToken $withAccessToken - Whether to return the access token after user account creation
     *  @return AuthRepository|UserResource
     */
    public function register(Request $request, $withAccessToken = AccessToken::RETURN_ACCESS_TOKEN)
    {
        /**
         *  Sometimes when registering we may include / exclude the password,
         *  depending on who is creating an account. Normally users on the
         *  USSD platform are not required to provide a password, however
         *  users using any other platform are required to provide their
         *  password.
         */
        if( $request->filled('password') ) {

            //  Encrypt the password (If provided)
            $request->merge(['password' => $this->getEncryptedRequestPassword($request)]);

        }

        /**
         *  If this user account is being created by an authenticated user.
         *  Remember that registration can be requested by a guest user
         *  (non-authenticated user) creating their own user account or
         *  by an authenticated user creating a user account on behalf
         *  of another user e.g user accounts created by a Super Admin
         *
         *  We therefore can check if this request is performed by an
         *  authenticated user so that we can capture information
         *  about this user performing this action.
         */
        if(auth()->check()) {

            //  Set the user id of the authenticated user
            $request->merge(['registered_by_user_id' => $this->getAuthUser()->id]);

        }

        //  Create a new user account
        parent::create($request);

        /**
         *  When creating the user's account, the user must provide the
         *  verification code of the new mobile number that they would
         *  like to associate their account with. This confirms that
         *  they own the mobile number specified. If the request is
         *  performed on the USSD platform or by a Super Admin,
         *  then we do not need to provide a verification code
         *  to verify this mobile number.
         *
         *  Revoke the mobile verification code (if provided).
         */
        $this->revokeRequestMobileVerificationCode($request);

        // If we should return the access token
        if ($withAccessToken == AccessToken::RETURN_ACCESS_TOKEN) {

            // Return the user account and access token
            return $this->getUserAndAccessToken();

        // If we should not return the access token
        } else {

            // Return the user account only
            return $this->transform();

        }
    }

    /**
     *  Show the terms and conditions
     *
     *  @return array
     */
    public function showTermsAndConditions()
    {
        $items = [
            [
                'name' => 'Accept Terms Of Service' ,
                'href' => route('terms.and.conditions.show')
            ],
        ];

        return [
            'title' => 'Terms & Conditions',
            'instruction' => 'Accept the following terms of service to continue using Perfect Order services',
            'confirmation' => 'I agree and accept these terms of service by proceeding to use this service.',
            'button' => 'Accept',
            'items' => $items
        ];
    }

    /**
     *  Accept the terms and conditions. This will grant
     *  the user access to consume routes that require
     *  the T&C's to be accepted first.
     *
     *  @return array
     */
    public function acceptTermsAndConditions()
    {
        //  If the user has not accepted the terms and conditions
        if( $this->model->accepted_terms_and_conditions == false ) {

            //  Accept the terms and conditions
            $accepted = $this->model->update([
                'accepted_terms_and_conditions' => true
            ]);

            //  If the terms and conditions were not accepted successfully
            if( !$accepted ) {

                //  Throw an Exception - Failed to accept
                throw new AcceptingTermsAndConditionsFailedException('Failed to accept the terms and conditions');

            }

        }

        return ['message' => 'Terms and conditions accepted successfully'];
    }

    /**
     *  Return the current user tokens - This could be the
     *  current authenticated user access tokens or the
     *  access tokens of the user being accessed by the
     *  Super Admin via the UserRepository class
     *
     *  @return array
     */
    public function showTokens()
    {
        //  Get the user tokens
        $tokens = $this->model->tokens;

        //  Get the user tokens
        $tranformedTokens = collect($tokens)->map(fn($token) => $token->only(['name', 'last_used_at', 'created_at']))->toArray();

        return [
            'tokens' => $tranformedTokens
        ];
    }

    /**
     *  Check if user account exists
     *
     *  @return UserResource
     */
    public function accountExists()
    {
        //  Set matching user (if exists)
        $this->setModel($this->getUserFromMobileNumber());

        //  Return user account
        return $this->transform();
    }

    /**
     *  Reset the account password and return the
     *  user account and access token
     *
     *  @param Request $request
     *  @return array
     */
    public function resetPassword(Request $request)
    {
        //  Set matching user
        $this->setModel($this->getUserFromMobileNumber());

        try {

            $this->updateAccountPassword($request);

            //  Return user account and access token
            return $this->getUserAndAccessToken();

        } catch (UpdatePasswordFailedException $e) {

            //  Throw an Exception - Account password reset failed
            throw new ResetPasswordFailedException;

        }
    }

    /**
     *  Generate mobile verification code
     *
     *  @param Request $request
     *  @return array
     */
    public function generateMobileVerificationCode(Request $request)
    {
        $mobileNumber = $request->input('mobile_number');

        //  Generate random 6 digit number
        $code = $this->generateRandomSixDigitCode();

        //  Update existing or create a new verification code
        $successful = MobileVerification::updateOrCreate(
            ['mobile_number' => $mobileNumber],
            ['mobile_number' => $mobileNumber, 'code' => $code]
        );

        if( $successful ) {

            return [
                'code' => $code
            ];

        }else{

            //  Throw an Exception - Mobile verification code generation failed
            throw new MobileVerificationCodeGenerationFailedException;

        }
    }

    /**
     *  Verify mobile verification code validity
     *
     *  @param Request $request
     *  @return array
     */
    public function verifyMobileVerificationCode(Request $request)
    {
        $code = $request->input('verification_code');
        $mobileNumber = $request->input('mobile_number');
        $isValid = MobileVerification::where('mobile_number', $mobileNumber)->where('code', $code)->exists();

        return ['is_valid' => $isValid];
    }

    /**
     *  Show mobile verification code
     *
     *  @param Request $request
     *  @return array
     */
    public function showMobileVerificationCode(Request $request)
    {
        $mobileNumber = $request->input('mobile_number');

        //  Get the matching mobile verification
        $mobileVerification = MobileVerification::where('mobile_number', $mobileNumber)->first();

        //  Return the mobile verification with limited information
        $data = collect($mobileVerification)->only(['code'])->toArray();

        return [
            'exists' => !empty($data),
            'code' => $data['code'] ?? null
        ];
    }

    /**
     *  Reset the mobile verification code so that
     *  the same code cannot be used again
     *
     *  @param Request $request
     *  @return void
     */
    public static function revokeRequestMobileVerificationCode(Request $request)
    {
        $hasProvidedMobileNumber = $request->filled('mobile_number');

        if( $hasProvidedMobileNumber ){

            $mobileNumber = $request->input('mobile_number');

            //  Revoke the mobile verificaiton code
            MobileVerification::where('mobile_number', $mobileNumber)->update(['code' => null]);
        }
    }

    /**
     *  Reset the mobile verification code so that
     *  the same code cannot be used again
     *
     *  @param User $user
     *  @return void
     */
    public static function revokeUserMobileVerificationCode(User $user)
    {
        //  Revoke the mobile verificaiton code
        MobileVerification::where('mobile_number', $user->mobile_number->withExtension)->update(['code' => null]);
    }

    /**
     *  Logout authenticated user
     *
     *  Understand that "$this->model" could represent the current authenticated user
     *  or any other user model that was set using the UserRepository class to allow
     *  the Super Admin to logout that specific user. Refer to the logout() method
     *  of the UserRepository class. This means we can use this method to logout
     *  the current authenticated user or the current authenticated user can
     *  logout other users via by logout method provided by the
     *  UserRepository class if they have the right permissions
     *
     *  @return array
     */
    public function logout()
    {
        //  Check if the Super Admin is trying to logout someone else
        $superAdminIsLoggingOutSomeoneElse = ($this->getAuthUser()->isSuperAdmin() && $this->getAuthUser()->id != $this->model->id);

        //  Check if we want to logout all devices
        $logoutAllDevices = request()->filled('everyone') && in_array(request()->input('everyone'), [true, 'true', 1, '1']);

        //  Check if we want to logout other devices
        $logoutOtherDevices = request()->filled('others') && in_array(request()->input('others'), [true, 'true', 1, '1']);

        //  If we want to logout from all devices or the Super Admin is logging out someone else
        if( $logoutAllDevices || $superAdminIsLoggingOutSomeoneElse ) {

            //  If the user that we are trying to logout is also a Super Admin
            if( $superAdminIsLoggingOutSomeoneElse && $this->model->isSuperAdmin() ){

                //  Restrict this logout action
                throw new LogoutOfSuperAdminRestrictedException;

            }

            // Revoke all tokens (Including current access token)
            $this->revokeAccessTokens();

        //  If we want to logout other devices except the current device
        }elseif( $logoutOtherDevices ) {

            // Revoke all tokens (Except the current access token)
            $this->revokeAccessTokensExceptTheCurrentAccessToken();

        //  If we want to logout the current device
        }else{

            //  Revoke the token that was used to authenticate the current request
            $this->revokeCurrentAccessToken();

        }

        return [
            'message' => 'Signed out successfully'
        ];
    }

    /**
     *  Update the account password using the password
     *  provided on the request body
     *
     *  @param Request $request
     *  @return void
     */
    private function updateAccountPassword(Request $request)
    {
        //  The selected fields are allowed to update account password
        $data = [

            //  Encrypt the password
            'password' => $this->getEncryptedRequestPassword($request),

            //  Set the mobile number verification datetime
            'mobile_number_verified_at' => Carbon::now()

        ];

        if( $this->model->update($data) ) {

            //  Revoke the mobile verification code
            $this->revokeRequestMobileVerificationCode($request);

        }else{

            //  Throw an Exception - Update account password failed
            throw new UpdatePasswordFailedException;

        }
    }

    /**
     *  Get and encrypt the request password
     *
     *  @param Request $request
     *  @return string
     */
    public static function getEncryptedRequestPassword(Request $request)
    {
        return bcrypt($request->input('password'));
    }

    /**
     *  Get the user and access token response
     *
     *  @return array
     */
    private function getUserAndAccessToken()
    {
        //  Check if the request is coming from the USSD server
        if( UssdService::verifyIfRequestFromUssdServer() ) {

            //  Create the access token
            $accessToken = $this->createAccessToken();

        }else{

            //  Create the access token while revoking previous access tokens i.e single device sign in
            $accessToken = $this->createAccessTokenWhileRevokingPreviousAccessTokens();

        }

        /**
         *  Temporarily login as this user so that we can make use of the auth()->user()
         *  method from the UserResource when transforming this authenticated user.
         *  We can archieve this by attaching the access token to the current
         *  request.
         */
        request()->headers->set('Authorization', 'Bearer ' . $accessToken);

        return [
            'user' => parent::transform(),
            'access_token' => $accessToken,
            'message' => 'Signed in successfullly'
        ];
    }

    /**
     *  Return the user matching the request mobile number
     *
     *  @return User
     */
    private function getUserFromMobileNumber()
    {
        $mobileNumber = request()->input('mobile_number');

        //  Check if we have a matching user
        return $this->model->searchMobileNumber($mobileNumber)->firstOrFail();
    }

    /**
     *  Create a new access token while revoking previous access tokens i.e single device sign in
     *
     *  Single device sign-in, also known as single session or single active session,
     *  refers to a security feature that allows a user to be logged in from only one
     *  device or session at a time. When a user logs in from a new device or session,
     *  any existing sessions are automatically invalidated, requiring the user to
     *  authenticate again on the new device.
     *
     *  @return string
     */
    private function createAccessTokenWhileRevokingPreviousAccessTokens()
    {
        /**
         *  To ensure that a user can only log into one device at a time,
         *  we can revoke the user's existing tokens before generating a
         *  new access token.
         */
        $this->revokeAccessTokens();

        //  Generate and return a new access token
        return $this->createAccessToken();
    }

    /**
     *  Revoke all tokens (Including current access token)
     *
     *  @return void
     */
    private function revokeAccessTokens()
    {
        $this->model->tokens()->delete();
    }

    /**
     *  Revoke the current access token that was used
     *  to authenticate the current request
     *
     *  @return void
     */
    private function revokeCurrentAccessToken()
    {
        $this->model->currentAccessToken()->delete();
    }

    /**
     *  Revoke all tokens (Except the current access token)
     *
     *  @return void
     */
    private function revokeAccessTokensExceptTheCurrentAccessToken()
    {
        $this->model->tokens()->where('id', '!=', $this->model->currentAccessToken()->id)->delete();
    }

    /**
     *  Create a new personal access token for the user.
     *
     *  @return string
     */
    private function createAccessToken()
    {
        /**
         *  Check if we have the device name provided on the
         *  request e.g "John's Iphone", otherwise use the
         *  current user's name e.g "John Doe"
         */
        $tokenName = (request()->filled('device_name'))
                     ? request()->input('device_name')
                     : $this->model->name;

        return $this->model->createToken($tokenName)->plainTextToken;
    }

    /**
     *  Return the FriendGroupRepository instance
     *
     *  @return FriendGroupRepository
     */
    public function friendGroupRepository()
    {
        return resolve(FriendGroupRepository::class);
    }

    /**
     *  Return the UserRepository instance
     *
     *  @return UserRepository
     */
    public function userRepository()
    {
        return resolve(UserRepository::class);
    }

}
