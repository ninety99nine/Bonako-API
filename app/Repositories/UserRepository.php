<?php

namespace App\Repositories;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Address;
use App\Models\AiMessage;
use App\Enums\AccessToken;
use App\Enums\CanSaveChanges;
use App\Models\FriendGroup;
use Illuminate\Http\Request;
use App\Traits\Base\BaseTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;
use App\Repositories\AuthRepository;
use App\Repositories\BaseRepository;
use App\Repositories\ShortcodeRepository;
use App\Repositories\AiMessageRepository;
use App\Repositories\FriendGroupRepository;
use Illuminate\Validation\ValidationException;
use Illuminate\Notifications\DatabaseNotification;
use App\Exceptions\DeleteOfSuperAdminRestrictedException;
use App\Models\AiAssistant;
use App\Models\SmsAlertActivityAssociation;
use App\Models\SubscriptionPlan;
use App\Services\AWS\AWSService;
use App\Services\Sms\SmsService;
use Http\Discovery\Exception\NotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserRepository extends BaseRepository
{
    use BaseTrait;
    protected $requiresConfirmationBeforeDelete = true;

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
     *  Return the repository user
     *
     *  @return User - Repository model user
     */
    public function getUser()
    {
        if($this->model instanceof User) {

            return $this->model;

        }else{

            throw new Exception('This repository model is not an instance of the User model');

        }
    }

    /**
     *  Return the Guest User ID
     *
     *  @return int
     */
    public function getGuestUserId()
    {
        return Cache::rememberForever('GUEST_USER_ID', function () {
            return $this->getGuestUser()->id;
        });
    }

    /**
     *  Return the Guest User instance
     *
     *  @return User
     */
    public function getGuestUser()
    {
        return Cache::rememberForever('GUEST_USER', function () {
            $guestUser = User::where('is_guest', '1')->first();
            return $guestUser ? $guestUser : $this->createGuestUser();
        });
    }

    /**
     *  Create the Guest User
     *
     *  @return User
     */
    public function createGuestUser()
    {
        return User::create([
            'first_name' => 'Guest',
            'last_name' => NULL,
            'mobile_number' => NULL,
            'last_seen_at' => NULL,
            'mobile_number_verified_at' => NULL,
            'accepted_terms_and_conditions' => false,
            'is_super_admin' => false,
            'is_guest' => true,
            'password' => NULL,
            'remember_token' => NULL,
        ]);
    }

    /**
     *  Return the AuthRepository instance
     *
     *  @return AuthRepository
     */
    public function authRepository()
    {
        return resolve(AuthRepository::class)->setModel(

            /*
             *  Set the current UserRepository instance Model as the
             *  AuthRepository instance Model so that we are
             *  strictly referencing the user set instead of
             *  the currently authenticated user who may
             *  be the Super Admin performing this
             *  action on behalf of another User.
             */
            $this->getUser()

        );
    }

    /**
     *  Return the OrderRepository instance
     *
     *  @return OrderRepository
     */
    public function orderRepository()
    {
        return resolve(OrderRepository::class);
    }

    /**
     *  Return the ReviewRepository instance
     *
     *  @return ReviewRepository
     */
    public function reviewRepository()
    {
        return resolve(ReviewRepository::class);
    }

    /**
     *  Return the StoreRepository instance
     *
     *  @return StoreRepository
     */
    public function storeRepository()
    {
        return resolve(StoreRepository::class);
    }

    /**
     *  Return the NotificationRepository instance
     *
     *  @return NotificationRepository
     */
    public function notificationRepository()
    {
        return resolve(NotificationRepository::class);
    }

    /**
     *  Return the AddressRepository instance
     *
     *  @return AddressRepository
     */
    public function addressRepository()
    {
        return resolve(AddressRepository::class);
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
     *  Return the AiAssistantRepository instance
     *
     *  @return AiAssistantRepository
     */
    public function aiAssistantRepository()
    {
        return resolve(AiAssistantRepository::class);
    }

    /**
     *  Return the SmsAlertRepository instance
     *
     *  @return SmsAlertRepository
     */
    public function smsAlertRepository()
    {
        return resolve(SmsAlertRepository::class);
    }

    /**
     *  Return the ShortcodeRepository instance
     *
     *  @return ShortcodeRepository
     */
    public function shortcodeRepository()
    {
        return resolve(ShortcodeRepository::class);
    }

    /**
     *  Return the TransactionRepository instance
     *
     *  @return TransactionRepository
     */
    public function transactionRepository()
    {
        return resolve(TransactionRepository::class);
    }

    /**
     *  Return the AiMessageRepository instance
     *
     *  @return AiMessageRepository
     */
    public function aiMessageRepository()
    {
        return resolve(AiMessageRepository::class);
    }

    /**
     *  Return the SubscriptionRepository instance
     *
     *  @return SubscriptionRepository
     */
    public function subscriptionRepository()
    {
        return resolve(SubscriptionRepository::class);
    }

    /**
     *  Return the SmsAlertActivityAssociationRepository instance
     *
     *  @return SmsAlertActivityAssociationRepository
     */
    public function smsAlertActivityAssociationRepository()
    {
        return resolve(SmsAlertActivityAssociationRepository::class);
    }

    /**
     *  Eager load relationships on the given model
     *
     *  @param \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder $model
     *  @return FriendGroupRepository
     */
    public function eagerLoadRelationships($model) {

        $relationships = [];

        if( !empty($relationships) ) {

            $model = ($model instanceof FriendGroup) ? $model->load($relationships) : $model->with($relationships);

        }

        return $this->setModel($model);

    }

    /**
     *  Return the user tokens
     *
     *  @return array
     */
    public function showTokens()
    {
        return $this->authRepository()->showTokens();
    }

    /**
     *  Show the terms and conditions
     *
     *  @return array
     */
    public function showTermsAndConditions()
    {
        return $this->authRepository()->showTermsAndConditions();
    }

    /**
     *  Accept the terms and conditions
     *
     *  @return array
     */
    public function acceptTermsAndConditions()
    {
        return $this->authRepository()->acceptTermsAndConditions();
    }

    /**
     *  Search for a user using the mobile number
     *
     *  @param Request $request
     *  @return array
     */
    public function searchUserByMobileNumber($request)
    {
        //  Get the specified mobile number
        $mobileNumber = request()->input('mobile_number');

        //  Get the user matching the specified mobile number
        $user = $this->model->where('mobile_number', $mobileNumber)->first();

        return [
            'exists' => !is_null($user),
            'user' => $user == null ? null : $this->setModel($user)->transform()
        ];
    }

    /**
     *  Register a new user account and return that created user account
     *
     *  @param Request $request
     *  @return UserResource
     */
    public function create($request)
    {
        return $this->authRepository()->register($request, AccessToken::DO_NOT_RETURN_ACCESS_TOKEN);
    }

    /**
     *  Update existing user account and return that updated user account
     *
     *  @param Request $request
     *  @return UserRepository
     */
    public function update($request)
    {
        //  If we provided a new password
        if( $request->filled('password') ) {

            //  Encrypt the password (If provided)
            $request->merge(['password' => AuthRepository::getEncryptedRequestPassword($request)]);

        }

        //  The selected fields are allowed to update an account
        $data = $request->only(['first_name', 'last_name', 'about_me', 'mobile_number', 'password']);

        //  Update existing account
        parent::update($data);

        /**
         *  When updating the user's mobile number, the user must provide the
         *  verification code of the new mobile number that they would like
         *  to change to. If the request is performed by the Super Admin,
         *  then we do not need to provide a verification code to verify
         *  this mobile number.
         *
         *  Revoke the mobile verification code (if provided)
         */
        AuthRepository::revokeRequestMobileVerificationCode($request);

        //  Return the Repository Class instance.
        return $this;
    }

    /**
     *  Delete existing repository model instance.
     *
     *  @param bool $forceDelete - Whether to permanently delete this resource
     *  @return array
     */
    public function delete($forceDelete = false) {

        $attemptingToDeleteSuperAdmin = ($this->model->isSuperAdmin() && $this->model->id != $this->getAuthUser()->id);

        //  Check if we are attempting to delete a Super Admin user other
        if( $attemptingToDeleteSuperAdmin ) {

            //  Restrict this delete action
            throw new DeleteOfSuperAdminRestrictedException;

        }

        //  Logout this user
        $this->logout(true);

        //  Delete the user
        return parent::delete();

    }



    /**
     *  Show the user profile photo
     *
     *  @return array
     */
    public function showProfilePhoto() {
        return [
            'profile_photo' => $this->model->profile_photo
        ];
    }

    /**
     *  Update the user profile photo
     *
     *  @param \Illuminate\Http\Request $request
     *
     *  @return UserRepository
     */
    public function updateProfilePhoto(Request $request) {

        //  Remove the exiting profile photo (if any) and save the new profile photo (if any)
        return $this->removeExistingProfilePhoto(CanSaveChanges::NO)->storeProfilePhoto($request);

    }

    /**
     *  Remove the existing user profile photo
     *
     *  @param CanSaveChanges $canSaveChanges - Whether to save the user changes after deleting the profile photo
     *  @return array | UserRepository
     */
    public function removeExistingProfilePhoto($canSaveChanges = CanSaveChanges::YES) {

        /**
         *  @var User $user
         */
        $user = $this->getUser();

        //  Check if we have an existing profile photo stored
        $hasExistingProfilePhoto = !empty($user->profile_photo);

        //  If the user has an existing profile photo stored
        if( $hasExistingProfilePhoto ) {

            //  Delete the profile photo file
            AWSService::delete($user->profile_photo);

        }

        //  If we should save these changes on the database
        if($canSaveChanges == CanSaveChanges::YES) {

            //  Save the user changes
            parent::update(['profile_photo' => null]);

            return [
                'message' => 'Profile photo deleted successfully'
            ];

        //  If we should not save these changes on the database
        }else{

            //  Remove the profile photo url reference from the user
            $user->profile_photo = null;

            //  Set the modified user
            $this->setModel($user);

        }

        return $this;

    }

    /**
     *  Store the user profile photo
     *
     *  @param \Illuminate\Http\Request $request
     *
     *  @return UserRepository|array
     */
    public function storeProfilePhoto(Request $request) {

        /**
         *  @var User $user
         */
        $user = $this->getUser();

        //  Check if we have a new profile photo provided
        $hasNewProfilePhoto = $request->hasFile('profile_photo');

        /**
         *  Save the new profile photo when the following condition is satisfied:
         *
         *  1) The profile photo is provided when we are updating the profile photo only
         *
         *  If the profile photo is provided while creating or updating the user as
         *  a whole, then the profile photo will be updated with the rest of the
         *  user details as a single query.
         *
         *  Refer to the saving() method of the UserObserver::class
         */
        $updatingTheUserProfilePhotoOnly = $request->routeIs('user.profile.photo.update') || $request->routeIs('auth.user.profile.photo.update');

        //  If we have a new profile photo provided
        if( $hasNewProfilePhoto ) {

            //  Save the profile photo on AWS and update the user with the profile photo url
            $user->profile_photo = AWSService::store('profile_photos', $request->profile_photo);

            //  Set the modified user
            $this->setModel($user);

            if( $updatingTheUserProfilePhotoOnly ) {

                //  Save the user changes
                $user->save();

            }

        }

        if( $updatingTheUserProfilePhotoOnly ) {

            //  Return the profile photo image url
            return ['profile photo' => $user->profile_photo];

        }

        return $this;

    }

    /**
     *  Generate mobile verification code
     *
     *  @param Request $request
     *  @return array
     */
    public function generateMobileVerificationCode(Request $request)
    {
        $request = $this->setUserMobileNumberOnRequest($request);
        return $this->authRepository()->generateMobileVerificationCode($request);
    }

    /**
     *  Verify mobile verification code validity
     *
     *  @param Request $request
     *  @return array
     */
    public function verifyMobileVerificationCode(Request $request)
    {
        $request = $this->setUserMobileNumberOnRequest($request);
        return $this->authRepository()->verifyMobileVerificationCode($request);
    }

    /**
     *  Show mobile verification code
     *
     *  @param Request $request
     *  @return array
     */
    public function showMobileVerificationCode(Request $request)
    {
        $request = $this->setUserMobileNumberOnRequest($request);
        return $this->authRepository()->showMobileVerificationCode($request);
    }

    /**
     *  Set the user's mobile number on the request
     *  if a mobile number hasn't been provided
     *
     *  @param Request $request
     *  @return Request
     */
    private function setUserMobileNumberOnRequest(Request $request) {

        //  Set the users mobile number on the request payload
        $request->merge(['mobile_number' => $this->model->mobile_number->withExtension]);

        return $request;

    }

    /**
     *  Logout the user
     *
     *  @param bool $logoutAllDevices - Indicate true/false that we want to logout all devices
     *  @return array
     */
    public function logout($logoutAllDevices = false)
    {
        //  Check if we want to logout all devices
        if($logoutAllDevices) {

            //  Indicate that we want to logout all devices
            request()->merge([ 'everyone' => true]);

        }

        return $this->authRepository()->logout();
    }

    /**
     *  Show the friend and friend group filters
     *
     *  @return array
     */
    public function showFriendAndFriendGroupFilters()
    {
        $filters = collect(['Friends', ...FriendGroup::FILTERS]);

        /**
         *  $result = [
         *      [
         *          'name' => 'Friends',
         *          'total' => 6000,
         *          'total_summarized' => '6k'
         *      ],
         *      [
         *          'name' => 'Groups',
         *          'total' => 2000,
         *          'total_summarized' => '2k'
         *      ],
         *      [
         *          'name' => 'Shared Groups',
         *          'total' => 1000k,
         *          'total_summarized' => '6k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) {

            $filter = strtolower($filter);

            if($filter == 'friends') {

                $total = $this->getUser()->friends()->count();

            }else if($filter == 'groups') {

                $total = $this->getUser()->friendGroups()->where('role', 'Creator')->count();

            }elseif($filter == 'shared groups') {

                $total = $this->getUser()->friendGroups()->where('role', 'Member')->where('shared', '1')->count();

            }

            return [
                'name' => ucwords($filter),
                'total' => $total,
                'total_summarized' => $this->convertNumberToShortenedPrefix($total)
            ];

        })->toArray();
    }

    /**
     *  Show the last selected friend
     *
     *  @return UserRepository
     */
    public function showLastSelectedFriend()
    {
        //  Get the last selected friend
        $lastSelectedFriend = $this->getUser()->friends()->orderByPivot('last_selected_at', 'DESC')->first();

        //  If the last selected friend exists
        if($lastSelectedFriend) {

            //  Return this repository instance with the last selected friend as the set model
            return $this->setModel($lastSelectedFriend);

        }else{

            //  Throw a not found exception
            throw new NotFoundHttpException;

        }
    }

    /**
     *  Update the last selected friends
     *
     *  @return void
     */
    public function updateLastSelectedFriends(Request $request)
    {
        //  Get the specified friend ids
        $friendUserIds = $request->input('friend_user_ids');

        // Update the last_selected_at datetime of the associated pivots
        DB::table('user_friend_association')
            ->whereIn('friend_user_id', $friendUserIds)
            ->where('user_id', $this->getUser()->id)
            ->update([
                'last_selected_at' => now()
            ]);
    }

    /**
     *  Show friends
     *
     *  @return UserRepository
     */
    public function showFriends()
    {
        $friends = $this->getUser()->friends()->latest();

        return $this->setModel($friends)->get();
    }

    /**
     *  Create friends
     *
     *  @return array
     */
    public function createFriends()
    {
        //  Get the specified mobile numbers
        $mobileNumbers = request()->input('mobile_numbers');

        //  Get the users that match the specified mobile numbers
        $currentFriends = $this->getUser()->friends()->whereIn('users.mobile_number', $mobileNumbers)->get();

        //  Get the mobile numbers of the current friends
        $currentFriendMobileNumbers = collect($currentFriends)->map(function($currentFriend) {
            return $currentFriend->mobile_number->withExtension;
        })->toArray();

        //  Get the mobile numbers of users that are not friends of this user
        $nonFriendsMobileNumbers = collect($mobileNumbers)->diff($currentFriendMobileNumbers)->toArray();

        //  If we have mobile numbers of users who are not recognised as friends
        if(count($nonFriendsMobileNumbers)) {

            //  Get the users that match the specified mobile numbers
            $newFriendIds = User::whereIn('users.mobile_number', $nonFriendsMobileNumbers)->pluck('id');

            //  Assign the users with their new roles and permissions
            $this->getUser()->friends()->attach($newFriendIds, [
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $totalFriends = count($newFriendIds);

            return [
                'message' => $totalFriends . ($totalFriends == 1 ? ' friend': ' friends') . ' added'
            ];

        }else{

            return [
                'message' => 'No new friends added'
            ];

        }
    }

    /**
     *  Remove friends
     *
     *  @return array
     */
    public function removeFriends()
    {
        //  Get the specified mobile numbers
        $mobileNumbers = request()->input('mobile_numbers');

        //  Get the users that match the specified mobile numbers
        $currentFriends = $this->getUser()->friends()->whereIn('users.mobile_number', $mobileNumbers)->get();

        //  Get the user ids that match the specified mobile numbers
        $currentFriendIds = collect($currentFriends)->pluck('id');

        //  Remove the current friends
        $this->getUser()->friends()->detach($currentFriendIds);

        //  If we have mobile numbers of users who are recognised as friends
        if($totalFriends = count($currentFriends)) {

            return [
                'message' => $totalFriends . ($totalFriends == 1 ? ' friend': ' friends') . ' removed'
            ];

        }else{

            return [
                'message' => 'No friends removed'
            ];

        }
    }













    /**
     *  Show the user friend group filters
     *
     *  @return array
     */
    public function showFriendGroupFilters()
    {
        return $this->friendGroupRepository()->showFriendGroupFilters($this->getUser());
    }

    /**
     *  Show the user friend groups
     *
     *  @return FriendGroupRepository
     */
    public function showFriendGroups()
    {
        return $this->friendGroupRepository()->showFriendGroups($this->getUser());
    }

    /**
     *  Show a user friend group
     *
     *  @param FriendGroup $friendGroup
     *  @return FriendGroupRepository
     */
    public function showFriendGroup(FriendGroup $friendGroup)
    {
        return $this->friendGroupRepository()->showFriendGroup($friendGroup);
    }

    /**
     *  Show the user's first created friend group
     *
     *  @return array
     */
    public function showFirstCreatedFriendGroup()
    {
        return $this->friendGroupRepository()->showFirstCreatedFriendGroup($this->getUser());
    }

    /**
     *  Show the user's last selected friend group
     *
     *  @return array
     */
    public function showLastSelectedFriendGroup()
    {
        return $this->friendGroupRepository()->showLastSelectedFriendGroup($this->getUser());
    }

    /**
     *  Update the last selected friend groups
     *
     *  @param Request $request
     *  @return array
     */
    public function updateLastSelectedFriendGroups(Request $request)
    {
        return $this->friendGroupRepository()->updateLastSelectedFriendGroups($this->getUser(), $request);
    }

    /**
     *  Create a new user friend group
     *
     *  @param Request $request
     *  @return array
     */
    public function createFriendGroup(Request $request)
    {
        return $this->friendGroupRepository()->createFriendGroup($this->getUser(), $request);
    }

    /**
     *  Update an existing user friend group
     *
     *  @param Request $request
     *  @return array
     *  @throws CannotUpdateFriendGroupException
     */
    public function updateFriendGroup(Request $request, FriendGroup $friendGroup)
    {
        return $this->friendGroupRepository()->setModel($friendGroup)->updateFriendGroup($this->getUser(), $request);
    }

    /**
     *  Delete a user friend group
     *
     *  @return array
     *  @throws CannotDeleteFriendGroupException
     */
    public function deleteFriendGroup(FriendGroup $friendGroup)
    {
        return $this->friendGroupRepository()->setModel($friendGroup)->deleteFriendGroup($this->getUser());
    }

    /**
     *  Delete many user friend groups
     *
     *  @param Request $request
     *  @return array
     */
    public function deleteManyFriendGroups(Request $request)
    {
        return $this->friendGroupRepository()->deleteManyFriendGroups($this->getUser(), $request);
    }

    /**
     *  Invite a single or multiple users on this friend group
     *
     *  @return array
     *  @throws CannotInviteFriendGroupMembersException
     */
    public function inviteFriendGroupMembers(FriendGroup $friendGroup, Request $request)
    {
        return $this->friendGroupRepository()->setModel($friendGroup)->inviteFriendGroupMembers($this->getUser(), $request);
    }

    /**
     *  Check invitations to join friend groups
     *
     *  @return array
     */
    public function checkInvitationsToJoinFriendGroups()
    {
        return $this->friendGroupRepository()->checkInvitationsToJoinFriendGroups($this->getUser());
    }

    /**
     *  Accept all invitations to join friend groups
     *
     *  @return array
     */
    public function acceptAllInvitationsToJoinFriendGroups()
    {
        return $this->friendGroupRepository()->acceptAllInvitationsToJoinFriendGroups($this->getUser());
    }

    /**
     *  Decline all invitations to join friend groups
     *
     *  @return array
     */
    public function declineAllInvitationsToJoinFriendGroups()
    {
        return $this->friendGroupRepository()->declineAllInvitationsToJoinFriendGroups($this->getUser());
    }

    /**
     *  Accept invitation to join friend group
     *
     *  @return array
     *  @throws InvitationAlreadyAcceptedException|InvitationAlreadyDeclinedException|InvalidInvitationException
     */
    public function acceptInvitationToJoinFriendGroup(FriendGroup $friendGroup)
    {
        return $this->friendGroupRepository()->setModel($friendGroup)->acceptInvitationToJoinFriendGroup($this->getUser());
    }

    /**
     *  Decline invitation to join friend group
     *
     *  @param User $user
     *  @return array
     *  @throws InvitationAlreadyAcceptedException|InvitationAlreadyDeclinedException|InvalidInvitationException
     */
    public function declineInvitationToJoinFriendGroup(FriendGroup $friendGroup)
    {
        return $this->friendGroupRepository()->setModel($friendGroup)->declineInvitationToJoinFriendGroup($this->getUser());
    }

    /**
     *  Remove a single or multiple users on this friend group
     *
     *  @return array
     *  @throws CannotRemoveYourselfAsFriendGroupMemberException|CannotRemoveYourselfAsFriendGroupCreatorException
     */
    public function removeFriendGroupMembers(FriendGroup $friendGroup)
    {
        return $this->friendGroupRepository()->setModel($friendGroup)->removeFriendGroupMembers($this->getUser());
    }

    /**
     *  Show the friend group member filters
     *
     *  @return array
     */
    public function showFriendGroupMemberFilters(FriendGroup $friendGroup)
    {
        return $this->friendGroupRepository()->setModel($friendGroup)->showFriendGroupMemberFilters($this->getUser());
    }

    /**
     *  Show the friend group members
     *
     *  @return UserRepository
     */
    public function showFriendGroupMembers(FriendGroup $friendGroup)
    {
        return $this->friendGroupRepository()->setModel($friendGroup)->showFriendGroupMembers($this->getUser());
    }

    /**
     *  Show the friend group store filters
     *
     *  @return array
     */
    public function showFriendGroupStoreFilters(FriendGroup $friendGroup)
    {
        return $this->friendGroupRepository()->setModel($friendGroup)->showFriendGroupStoreFilters($this->getUser());
    }

    /**
     *  Show the friend group stores
     *
     *  @return StoreRepository
     */
    public function showFriendGroupStores(FriendGroup $friendGroup)
    {
        return $this->friendGroupRepository()->setModel($friendGroup)->showFriendGroupStores($this->getUser());
    }

    /**
     *  Add friend group stores
     *
     *  @param Request $request
     *  @return array
     *  @throws CannotAddStoresToFriendGroupException
     */
    public function addFriendGroupStores(FriendGroup $friendGroup, Request $request)
    {
        return $this->friendGroupRepository()->setModel($friendGroup)->addFriendGroupStores($this->getUser(), $request);
    }

    /**
     *  Remove friend group stores
     *
     *  @param Request $request
     *  @return array
     *  @throws CannotRemoveStoresToFriendGroupException
     */
    public function removeFriendGroupStores(FriendGroup $friendGroup, Request $request)
    {
        return $this->friendGroupRepository()->setModel($friendGroup)->removeFriendGroupStores($this->getUser(), $request);
    }

    /**
     *  Show the friend group order filters
     *
     *  @return array
     */
    public function showFriendGroupOrderFilters(FriendGroup $friendGroup)
    {
        return $this->friendGroupRepository()->setModel($friendGroup)->showFriendGroupOrderFilters();
    }

    /**
     *  Show the friend group orders
     *
     *  @return OrderRepository
     */
    public function showFriendGroupOrders(FriendGroup $friendGroup)
    {
        return $this->friendGroupRepository()->setModel($friendGroup)->showFriendGroupOrders();
    }

















    /**
     *  Show the user notification filters
     *
     *  @return array
     */
    public function showNotificationFilters()
    {
        $filters = collect(User::NOTIFICATION_FILTERS);

        /**
         *  $result = [
         *      [
         *          'name' => 'All',
         *          'total' => 3000,
         *          'total_summarized' => '2k'
         *      ],
         *      [
         *          'name' => 'Read',
         *          'total' => 2000k,
         *          'total_summarized' => '6k'
         *      ],
         *      [
         *          'name' => 'Unread',
         *          'total' => 1000k,
         *          'total_summarized' => '6k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) {

            $notifications = $this->getUser()->notifications();
            $total = $this->queryNotificationsByFilter($notifications, $filter)->count();

            return [
                'name' => ucwords($filter),
                'total' => $total,
                'total_summarized' => $this->convertNumberToShortenedPrefix($total)
            ];

        })->toArray();
    }

    /**
     *  Show the user notifications
     *
     *  @return NotificationRepository
     */
    public function showNotifications()
    {
        //  Get the request filter
        $filter = request()->input('filter');

        //  Query the user notifications
        $notifications = $this->getUser()->notifications();

        //  Query the notifications by the filter (If provided)
        $notifications = $this->queryNotificationsByFilter($notifications, $filter);

        return $this->notificationRepository()->setModel($notifications)->get();
    }

    /**
     *  Query the notifications by the specified filter
     *
     *  @param Builder $notifications
     *  @param string $filter - The filter to query the notifications
     */
    public function queryNotificationsByFilter($notifications, $filter)
    {
        //  Normalize the filter
        $filter = $this->separateWordsThenLowercase($filter);

        if($filter == 'read') {

            return $notifications->whereNotNull('read_at');

        }elseif($filter == 'unread') {

            return $notifications->whereNull('read_at');

        }elseif($filter == 'invitations') {

            return $notifications->whereIn('type', [
                'App\Notifications\Users\InvitationToFollowStoreCreated',
                'App\Notifications\Users\InvitationToFollowStoreAccepted',
                'App\Notifications\Users\InvitationToFollowStoreDeclined',
                'App\Notifications\Users\InvitationToJoinStoreTeamCreated',
                'App\Notifications\Users\InvitationToJoinStoreTeamAccepted',
                'App\Notifications\Users\InvitationToJoinStoreTeamDeclined',
            ]);

        }elseif($filter == 'orders') {

            return $notifications->whereIn('type', [
                'App\Notifications\Orders\OrderSeen',
                'App\Notifications\Orders\OrderCreated',
                'App\Notifications\Orders\OrderStatusUpdated'
            ]);

        }elseif($filter == 'friend groups') {

            return $notifications->whereIn('type', [
                'App\Notifications\FriendGroups\FriendGroupUserAdded',
                'App\Notifications\FriendGroups\FriendGroupStoreAdded',
                'App\Notifications\FriendGroups\FriendGroupUserRemoved',
                'App\Notifications\FriendGroups\FriendGroupStoreRemoved',
            ]);

        }else{

            return $notifications;

        }
    }

    /**
     *  Count the user notifications
     *
     *  @return NotificationRepository
     */
    public function countNotifications()
    {
        //  Query the total unread notifications
        $unreadNotifications = $this->getUser()->unreadNotifications()->count();

        return [
            'total_unread_notifications' => $unreadNotifications,
        ];
    }

    /**
     *  Mark the user notification as read
     *
     *  @return NotificationRepository
     */
    public function markNotificationsAsRead()
    {
        //  Mark the unread notifications
        $this->getUser()->unreadNotifications()->update(['read_at' => now()]);

        return [
            'message' => 'Marked as read'
        ];
    }

    /**
     *  Show the user notification
     *
     *  @param DatabaseNotification $notification
     *  @return NotificationRepository
     */
    public function showNotification(DatabaseNotification $notification)
    {
        return $this->notificationRepository()->setModel($notification);
    }

    /**
     *  Show the user notification
     *
     *  @param DatabaseNotification $notification
     *  @return NotificationRepository
     */
    public function markNotificationAsRead(DatabaseNotification $notification)
    {
        //  Mark the unread notification
        $notification->markAsRead();

        return [
            'message' => 'Marked as read'
        ];
    }






    /**
     *  Show the user addresses
     *
     *  @return AddressRepository
     */
    public function showAddresses()
    {
        //  Query the user addresses
        $addresses = $this->getUser()->addresses()->latest('updated_at');

        //  If the current authenticated user id does not match the current repository user id
        if( $this->getAuthUser()->id != $this->getUser()->id ) {

            //  Query the shared addresses (We want to only expose the shared addresses)
            $addresses = $addresses->shared();

        }

        return $this->addressRepository()->setModel($addresses)->get();
    }

    /**
     *  Create a user address
     *
     *  @param Request $request
     *  @return AddressRepository
     */
    public function createAddress(Request $request)
    {
        $request->merge(['user_id' => $this->getUser()->id]);
        return $this->addressRepository()->create($request);
    }

    /**
     *  Show the user address
     *
     *  @param Address $address
     *  @return AddressRepository
     */
    public function showAddress(Address $address)
    {
        return $this->addressRepository()->setModel($address);
    }

    /**
     *  Update the user address
     *
     *  @param Request $request
     *  @param Address $address
     *  @return AddressRepository
     */
    public function updateAddress(Request $request, Address $address)
    {
        return $this->addressRepository()->setModel($address)->update($request);
    }

    /**
     *  Delete the user address
     *
     *  @param Address $address
     *  @return array
     */
    public function deleteAddress(Address $address)
    {
        return $this->addressRepository()->setModel($address)->delete();
    }





    /**
     *  Show user order filters
     *
     *  @return array
     */
    public function showUserOrderFilters()
    {
        return $this->orderRepository()->showUserOrderFilters($this->getUser());
    }

    /**
     *  Show user orders
     *
     *  @return OrderRepository
     */
    public function showUserOrders()
    {
        return $this->orderRepository()->showUserOrders($this->getUser());
    }

    /**
     *  Show user review filters
     *
     *  @return array
     */
    public function showReviewFilters()
    {
        return $this->reviewRepository()->showUserReviewFilters($this->getUser());
    }

    /**
     *  Show user reviews
     *
     *  @return ReviewRepository
     */
    public function showReviews()
    {
        return $this->reviewRepository()->showUserReviews($this->getUser());
    }

    /**
     *  Show the user's first store
     *
     *  @return array
     */
    public function showUserFirstCreatedStore()
    {
        return $this->storeRepository()->showUserFirstCreatedStore($this->getUser());
    }

    /**
     *  Show user store filters
     *
     *  @return array
     */
    public function showStoreFilters()
    {
        return $this->storeRepository()->showUserStoreFilters($this->getUser());
    }

    /**
     *  Show user stores
     *
     *  @return StoreRepository
     */
    public function showStores()
    {
        return $this->storeRepository()->showUserStores($this->getUser());
    }

    /**
     *  Create user store
     *
     *  @return StoreRepository
     */
    public function createStore(Request $request)
    {
        return $this->storeRepository()->create($request);
    }

    /**
     *  Join a store using the team member join code
     *
     *  @param Request $request
     *  @return array
     */
    public function joinStore(Request $request)
    {
        $joinCode = $request->input('team_member_join_code');

        //  Get the store that matches the invitation join code
        $store = $this->getUser()->stores()->invitedToJoinTeam()->where('team_member_join_code', $joinCode)->first();

        //  If the store exists
        if($store) {

            //  Accept the invitation to join this store
            $this->storeRepository()->setModel($store)->updateInvitationToJoinTeamStatus('Joined');

            //  Return this repository instance with the last selected friend as the set model
            return [
                'message' => 'You have joined ' . $store->name
            ];

        }else{

            //  Throw an Exception - The join code is invalid
            throw ValidationException::withMessages(['team_member_join_code' => 'The join code does not match any invitation']);

        }
    }

    /**
     *  Request the AI Assistant payment shortcode
     *
     *  This will allow the user to dial the shortcode and pay via USSD
     *
     *  @return StoreRepository
     */
    public function generateAiAssistantPaymentShortcode(Request $request)
    {
        $user = $this->getUser();

        //  Get the User ID that this shortcode is reserved for
        $reservedForUserId = $user->id;

        //  Get the AI Assistant information for the user
        $aiAssistant = $this->aiAssistantRepository()->showAiAssistant($user)->model;

        //  Request a payment shortcode for this AI Assistant
        $shortcodeRepository = $this->shortcodeRepository()->generatePaymentShortcode($aiAssistant, $reservedForUserId);

        //  If we want to return the AI Assistant model with the payment shortcode embedded
        if( $request->input('embed') ) {

            //  Set the AI Assistant as the repository model with the payment shortcode
            return $this->aiAssistantRepository(

                //  Load the payment shortcode on this AI Assistant
                $aiAssistant->load('authPaymentShortcode')

            );

        //  If we want to return the payment shortcode alone
        }else{

            //  Return the shortcode repository
            return $shortcodeRepository;

        }
    }

    /**
     *  Show the AI Assistant subscriptions
     *
     *  @return SubscriptionRepository
     *  @throws ModelNotFoundException
     */
    public function showAiAssistantSubscriptions()
    {
        //  Get the user
        $user = $this->getUser();

        //  Get the AI Assistant information for the user
        $aiAssistant = $this->aiAssistantRepository()->showAiAssistant($user)->model;

        //  Return the subscription repository
        return $this->subscriptionRepository()->setModel($aiAssistant->subscriptions())->get();
    }

    /**
     *  Calculate AI access subscription amount
     *
     *  @param Request $request
     *  @return array
     */
    public function calculateAiAccessSubscriptionAmount(Request $request)
    {
        //  Get the Subscription Plan ID
        $subscriptionPlanId = $request->input('subscription_plan_id');

        //  Get the Subscription Plan
        $subscriptionPlan = SubscriptionPlan::find($subscriptionPlanId);

        //  Calculate the user access subscription amount
        $amount = $this->subscriptionRepository()->calculateSubscriptionAmount($request, $subscriptionPlan);

        return [
            'calculation' => $this->convertToMoneyFormat($amount, 'BWP')
        ];
    }

    /**
     *  Create AI Assistant subscription
     *
     *  A subscription enables the user access to the AI Assistant.
     *
     *  @return SubscriptionRepository | AiAssistantRepository
     *  @throws ModelNotFoundException
     */
    public function createAiAssistantSubscription(Request $request)
    {
        $user = $this->getUser();

        //  Get the AI Assistant information for the user
        $aiAssistant = $this->aiAssistantRepository()->showAiAssistant($user)->model;

        //  Get the latest subscription matching the given user to this AiAssistant model
        $latestSubscription = $aiAssistant->subscriptions()->where('user_id', $user->id)->latest()->first();

        //  Create a subscription
        $subscriptionRepository = $this->subscriptionRepository()->create($aiAssistant, $request, $latestSubscription);

        //  Get the subscription
        $subscription = $subscriptionRepository->model;

        //  Get the subscription end datetime
        $subscriptionExpiresAt = $subscription->end_at;

        //  Get the subscription transaction amount
        $transactionAmount = $subscription->transaction->amount->amount;

        //  Calculate the new paid tokens
        $newPaidTokens = $transactionAmount * AiAssistant::PAID_TOKEN_RATE;

        //  If the remaining paid tokens have not yet expired
        if(Carbon::parse($aiAssistant->remaining_paid_tokens_expire_at)->isFuture()) {

            //  Set the remaining paid tokens
            $remainingPaidTokens = ($aiAssistant->remaining_paid_tokens) + $newPaidTokens;

        }else{

            //  Set the remaining paid tokens
            $remainingPaidTokens = $newPaidTokens;

        }

        //  The remaining paid tokens after the last subscription are the current remaining paid tokens
        //  before the user starts consuming these remaining paid tokens.
        $remainingPaidTokensAfterLastSubscription = $remainingPaidTokens;

        //  Update the AI Assistant model
        $aiAssistant->update([
            'requires_subscription' => false,
            'remaining_paid_tokens' => $remainingPaidTokens,
            'remaining_paid_tokens_expire_at' => $subscriptionExpiresAt,
            'remaining_paid_tokens_after_last_subscription' => $remainingPaidTokensAfterLastSubscription,
        ]);

        // Send sms to user that their subscription was paid successfully
        SmsService::sendOrangeSms(
            $subscription->craftSubscriptionSuccessfulSmsMessageForUser($user, $aiAssistant),
            $user->mobile_number->withExtension,
            null, null, null
        );

        //  If we want to return the AI Assistant model with the subscription embedded
        if( $request->input('embed') ) {

            /**
             *  Set the AI Assistant as the repository model with the
             *  current authenticated user's active subscription
             */
            return $this->setModel(

                //  Load the subscription on this AI Assistant
                $aiAssistant->load(['authActiveSubscription'])

            );

        //  If we want to return the subscription alone
        }else{

            //  Return the subscription repository model
            return $subscriptionRepository;

        }
    }

    /**
     *  Show AI Assistant
     *
     *  @return AiAssistantRepository
     */
    public function showAiAssistant()
    {
        return $this->aiAssistantRepository()->showAiAssistant($this->getUser());
    }

    /**
     *  Show AI messages
     *
     *  @param Request $request
     *  @return AiMessageRepository
     */
    public function showAiMessages(Request $request)
    {
        $aiMessages = $this->getUser()->aiMessages()->latest();

        if($categoryId = $request->input('category_id')) {
            $aiMessages = $aiMessages->where('category_id', $categoryId);
        }

        return $this->aiMessageRepository()->setModel($aiMessages)->get();
    }

    /**
     *  Create an AI message
     *
     *  @param Request $request
     *  @return AiMessageRepository|null
     */
    public function createAiMessage(Request $request)
    {
        return $this->aiMessageRepository()->createUserAiMessage($this->getUser(), $request);
    }

    /**
     *  Show an AI message
     *
     *  @param AiMessage $aiMessage
     *  @return AiMessageRepository
     */
    public function showAiMessage(AiMessage $aiMessage)
    {
        return $this->aiMessageRepository()->setModel($aiMessage);
    }

    /**
     *  Update an AI message
     *
     *  @param Request $request
     *  @param AiMessage $aiMessage
     *  @return AiMessageRepository
     */
    public function updateAiMessage(Request $request, AiMessage $aiMessage)
    {
        return $this->aiMessageRepository()->setModel($aiMessage)->update($request);
    }

    /**
     *  Delete an AI message
     *
     *  @param AiMessage $aiMessage
     *  @return array
     */
    public function deleteAiMessage(AiMessage $aiMessage)
    {
        return $this->aiMessageRepository()->setModel($aiMessage)->delete();
    }

    /**
     *  Show Sms Alert
     *
     *  @return SmsAlertRepository
     */
    public function showSmsAlert()
    {
        return $this->smsAlertRepository()->showSmsAlert($this->getUser());
    }

    /**
     *  Request the Sms Alert payment shortcode
     *
     *  This will allow the user to dial the shortcode and pay via USSD
     *
     *  @return StoreRepository
     */
    public function generateSmsAlertPaymentShortcode(Request $request)
    {
        $user = $this->getUser();

        //  Get the User ID that this shortcode is reserved for
        $reservedForUserId = $user->id;

        //  Get the SMS Alert information for the user
        $smsAlert = $this->showSmsAlert()->model;

        //  Request a payment shortcode for this SMS Alert
        $shortcodeRepository = $this->shortcodeRepository()->generatePaymentShortcode($smsAlert, $reservedForUserId);

        //  If we want to return the SMS Alert model with the payment shortcode embedded
        if( $request->input('embed') ) {

            //  Set the SMS Alert as the repository model with the payment shortcode
            return $this->smsAlertRepository(

                //  Load the payment shortcode on this SMS Alert
                $smsAlert->load('authPaymentShortcode')

            );

        //  If we want to return the payment shortcode alone
        }else{

            //  Return the shortcode repository
            return $shortcodeRepository;

        }
    }

    /**
     *  Show the SMS Alert transactions
     *
     *  @return TransactionRepository
     */
    public function showSmsAlertTransactions()
    {
        //  Get the SMS Alert information for the user
        $smsAlert = $this->showSmsAlert()->model;

        //  Return the transaction repository
        return $this->transactionRepository()->setModel($smsAlert->transactions()->latest())->get();
    }

    /**
     *  Create SMS Alert transaction
     *
     *  This grants the user sms credits to be used for SMS Alerts
     *
     *  @return SubscriptionRepository | AiAssistantRepository
     */
    public function createSmsAlertTransaction(Request $request)
    {
        //  Get the SMS Alert information for the user
        $smsAlert = $this->showSmsAlert()->model;

        //  Get the Subscription Plan ID
        $subscriptionPlanId = $request->input('subscription_plan_id');

        //  Get the Subscription Plan
        $subscriptionPlan = SubscriptionPlan::find($subscriptionPlanId);

        //  Create a transaction
        $transactionRepository = $this->transactionRepository()->createSmsAlertTransaction($smsAlert, $subscriptionPlan, $request);

        //  Get the transaction
        $transaction = $transactionRepository->model;

        //  Get the Subscription Plan credits
        $credits = $subscriptionPlan->metadata['credits'];

        //  Update the SMS Alert credits
        $smsAlert->update(['sms_credits' => ($smsAlert->sms_credits + $credits)]);

        // Send sms to user that their transaction was paid successfully
        SmsService::sendOrangeSms(
            $smsAlert->craftSuccessfulPaymentSmsMessage($this->getUser(), $transaction),
            $this->getUser()->mobile_number->withExtension,
            null, null, null
        );

        //  If we want to return the SMS Alert model with the transaction embedded
        if( $request->input('embed') ) {

            /**
             *  Set the SMS Alert as the repository model
             *  with the latest transaction
             */
            return $this->setModel(

                //  Load the latest transaction on this SMS Alert
                $smsAlert->load(['latestTransaction'])

            );

        //  If we want to return the transaction alone
        }else{

            //  Return the transaction repository model
            return $transactionRepository;

        }
    }

    /**
     *  Calculate SMS Alert transaction amount
     *
     *  @param Request $request
     *  @return array
     */
    public function calculateSmsAlertTransactionAmount(Request $request)
    {
        //  Get the credits required
        $credits = $request->input('credits');

        //  Get the Subscription Plan ID
        $subscriptionPlanId = $request->input('subscription_plan_id');

        //  Get the Subscription Plan
        $subscriptionPlan = SubscriptionPlan::find($subscriptionPlanId);

        //  Calculate the transaction amount
        $amount = $subscriptionPlan->price->amount * $credits;

        return [
            'calculation' => $this->convertToMoneyFormat($amount, 'BWP')
        ];
    }

    /**
     *  Update the sms alert activity association
     *
     *  @param SmsAlertActivityAssociation $smsAlertActivityAssociation
     *  @param Request $request
     *  @return SmsAlertActivityAssociationRepository
     */
    public function updateSmsAlertActivityAssociation(SmsAlertActivityAssociation $smsAlertActivityAssociation, Request $request)
    {
        return $this->smsAlertActivityAssociationRepository()->setModel($smsAlertActivityAssociation)->update($request);
    }




    /**
     *  Show the user resource totals
     *
     *  @return array
     */
    public function showResourceTotals()
    {
        //  Get the SMS Alert information for the user
        $smsAlert = $this->showSmsAlert()->model;

        $totalSmsAlertCredits = $smsAlert->sms_credits;
        $totalOrders = $this->getUser()->orders()->count();
        $totalReviews = $this->getUser()->reviews()->count();
        $totalNotifications = $this->getUser()->notifications()->count();

        $totalGroupsJoined = $this->getUser()->friendGroups()->count();
        $totalGroupsJoinedAsCreator = $this->getUser()->friendGroups()->joinedGroupAsCreator()->count();
        $totalGroupsJoinedAsNonCreator = $this->getUser()->friendGroups()->joinedGroupAsNonCreator()->count();
        $totalGroupsInvitedToJoinAsGroupMember = $this->getUser()->friendGroups()->invitedToJoinGroup()->count();

        $totalStoresAsFollower = $this->getUser()->storesAsFollower()->count();
        $totalStoresAsCustomer = $this->getUser()->storesAsCustomer()->count();
        $totalStoresAsRecentVisitor = $this->getUser()->storesAsRecentVisitor()->count();

        $totalStoresJoinedAsTeamMember = $this->getUser()->storesAsTeamMember()->joinedTeam()->count();
        $totalStoresJoinedAsCreator = $this->getUser()->storesAsTeamMember()->joinedTeamAsCreator()->count();
        $totalStoresJoinedAsNonCreator = $this->getUser()->storesAsTeamMember()->joinedTeamAsNonCreator()->count();
        $totalStoresInvitedToJoinAsTeamMember = $this->getUser()->storesAsTeamMember()->invitedToJoinTeam()->count();

        return [
            'totalOrders' => $totalOrders,
            'totalReviews' => $totalReviews,
            'totalNotifications' => $totalNotifications,
            'totalSmsAlertCredits' => $totalSmsAlertCredits,
            'totalStoresAsFollower' => $totalStoresAsFollower,
            'totalStoresAsCustomer' => $totalStoresAsCustomer,

            'totalGroupsJoined' => $totalGroupsJoined,
            'totalGroupsJoinedAsCreator' => $totalGroupsJoinedAsCreator,
            'totalGroupsJoinedAsNonCreator' => $totalGroupsJoinedAsNonCreator,
            'totalGroupsInvitedToJoinAsGroupMember' => $totalGroupsInvitedToJoinAsGroupMember,

            'totalStoresJoinedAsCreator' => $totalStoresJoinedAsCreator,
            'totalStoresAsRecentVisitor' => $totalStoresAsRecentVisitor,
            'totalStoresJoinedAsTeamMember' => $totalStoresJoinedAsTeamMember,
            'totalStoresJoinedAsNonCreator' => $totalStoresJoinedAsNonCreator,
            'totalStoresInvitedToJoinAsTeamMember' => $totalStoresInvitedToJoinAsTeamMember,
        ];
    }
}
