<?php

namespace App\Repositories;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Address;
use App\Models\AiMessage;
use App\Enums\AccessToken;
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
use App\Models\SubscriptionPlan;
use Http\Discovery\Exception\NotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
     *  Return the ShortcodeRepository instance
     *
     *  @return ShortcodeRepository
     */
    public function shortcodeRepository()
    {
        return resolve(ShortcodeRepository::class);
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
        $data = $request->only(['first_name', 'last_name', 'mobile_number', 'password']);

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
        return $this->friendGroupRepository()->showUserFriendGroupFilters($this->getUser());
    }

    /**
     *  Show the user friend groups
     *
     *  @return FriendGroupRepository
     */
    public function showFriendGroups()
    {
        return $this->friendGroupRepository()->showUserFriendGroups($this->getUser());
    }

    /**
     *  Create a user friend group
     *
     *  @param Request $request
     *  @return array
     */
    public function createFriendGroup(Request $request)
    {
        return $this->friendGroupRepository()->createUserFriendGroup($this->getUser(), $request);
    }

    /**
     *  Show a user friend group
     *
     *  @param FriendGroup $friendGroup
     *  @return FriendGroupRepository
     */
    public function showFriendGroup(FriendGroup $friendGroup)
    {
        return $this->friendGroupRepository()->setModel($friendGroup);
    }

    /**
     *  Update a user friend group
     *
     *  @param Request $request
     *  @return FriendGroupRepository
     */
    public function updateFriendGroup(Request $request, FriendGroup $friendGroup)
    {
        return $this->friendGroupRepository()->setModel($friendGroup)->updateUserFriendGroup($request, $this->getUser());
    }

    /**
     *  Delete a friend group
     *
     *  @param FriendGroup $friendGroup
     *  @return array
     */
    public function deleteFriendGroup(FriendGroup $friendGroup)
    {
        return $this->friendGroupRepository()->setModel($friendGroup)->delete();
    }

    /**
     *  Delete many friend groups
     *
     *  @param Request $request
     *  @return array
     */
    public function deleteManyFriendGroups(Request $request)
    {
        return $this->friendGroupRepository()->deleteManyUserFriendGroups($request, $this->getUser());
    }


    /**
     *  Show the last selected friend
     *
     *  @return FriendGroupRepository|null
     */
    public function showLastSelectedFriendGroup()
    {
        return $this->friendGroupRepository()->showLastSelectedUserFriendGroup($this->getUser());
    }

    /**
     *  Show the last selected friend
     *
     *  @param Request $request
     *  @return array
     */
    public function updateLastSelectedFriendGroups(Request $request)
    {
        return $this->friendGroupRepository()->updateLastSelectedUserFriendGroups($request, $this->getUser());
    }

    /**
     *  Show the friend group members
     *
     *  @param Request $request
     *  @param FriendGroup $friendGroup
     *  @return UserRepository
     */
    public function showFriendGroupMembers(Request $request, FriendGroup $friendGroup)
    {
        return $this->friendGroupRepository()->setModel($friendGroup)->showFriendGroupMembers($request);
    }

    /**
     *  Remove friend group members
     *
     *  @param Request $request
     *  @param FriendGroup $friendGroup
     *  @return array
     */
    public function removeFriendGroupMembers(Request $request, FriendGroup $friendGroup)
    {
        return $this->friendGroupRepository()->setModel($friendGroup)->removeFriendGroupMembers($request, $this->getUser());
    }

    /**
     *  Show friend group stores
     *
     *  @param FriendGroup $friendGroup
     *  @return StoreRepository
     */
    public function showFriendGroupStores(FriendGroup $friendGroup)
    {
        return $this->friendGroupRepository()->setModel($friendGroup)->showFriendGroupStores();
    }

    /**
     *  Add friend group stores
     *
     *  @param Request $request
     *  @param FriendGroup $friendGroup
     *  @return array
     */
    public function addFriendGroupStores(Request $request, FriendGroup $friendGroup)
    {
        return $this->friendGroupRepository()->setModel($friendGroup)->addFriendGroupStores($request, $this->getUser());
    }

    /**
     *  Remove friend group stores
     *
     *  @param Request $request
     *  @param FriendGroup $friendGroup
     *  @return array
     */
    public function removeFriendGroupStores(Request $request, FriendGroup $friendGroup)
    {
        return $this->friendGroupRepository()->setModel($friendGroup)->removeFriendGroupStores($request);
    }

    /**
     *  Show friend group orders
     *
     *  @param FriendGroup $friendGroup
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
    public function showOrderFilters()
    {
        return $this->orderRepository()->showUserOrderFilters($this->getUser());
    }

    /**
     *  Show user orders
     *
     *  @return OrderRepository
     */
    public function showOrders()
    {
        return $this->orderRepository()->showUserOrders($this->getUser());
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
     *  Show the AI Assistant subscriptions
     *
     *  @return SubscriptionRepository
     *  @throws ModelNotFoundException
     */
    public function showAiAssistantSubscriptions()
    {
        //  Get the user
        $user = $this->getUser();

        //  Get the existing AI Assistant information for the user
        $aiAssistant = $this->aiAssistantRepository()->showAiAssistant($user)->model;

        //  Calculate the user access subscription amount
        return $this->subscriptionRepository()->setModel($aiAssistant->subscriptions())->get();
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

        //  Get the existing AI Assistant information for the user
        $aiAssistant = $this->aiAssistantRepository()->showAiAssistant($user)->model;

        //  Request a payment shortcode for this AI Assistant
        $shortcodeRepository = $this->shortcodeRepository()->generatePaymentShortcode($aiAssistant, $reservedForUserId);

        //  If we want to return the AI Assistant model with the payment shortcode embedded
        if( $request->input('embed') ) {

            return $this->aiAssistantRepository()->setModel(

                //  Load the subscription on this AI Assistant model
                $aiAssistant->load(['subscription' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }])

            );

        //  If we want to return the payment shortcode alone
        }else{

            //  Return the shortcode repository
            return $shortcodeRepository;

        }
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

        //  Get the existing AI Assistant information for the user
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

        //  If we want to return the AI Assistant model with the subscription embedded
        if( $request->input('embed') ) {

            return $this->aiAssistantRepository()->setModel(

                //  Load the subscription on this AI Assistant model
                $aiAssistant->load(['subscription' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }])

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
     *  Show the user resource totals
     *
     *  @return array
     */
    public function showResourceTotals()
    {
        $totalGroups = $this->getUser()->friendGroups()->count();
        $totalNotifications = $this->getUser()->notifications()->count();
        $totalStoresFollowing = $this->getUser()->storesAsFollower()->count();
        $totalStoresRecentlyVisited = $this->getUser()->storesAsRecentVisitor()->count();
        $totalStoresJoined = $this->getUser()->storesAsTeamMember()->joinedTeam()->count();
        $totalStoresJoinedAsCreator = $this->getUser()->storesAsTeamMember()->joinedTeamAsCreator()->count();
        $totalStoresJoinedAsNonCreator = $this->getUser()->storesAsTeamMember()->joinedTeamAsNonCreator()->count();

        return [
            'totalGroups' => $totalGroups,
            'totalStoresJoined' => $totalStoresJoined,
            'totalNotifications' => $totalNotifications,
            'totalStoresFollowing' => $totalStoresFollowing,
            'totalStoresJoinedAsCreator' => $totalStoresJoinedAsCreator,
            'totalStoresRecentlyVisited' => $totalStoresRecentlyVisited,
            'totalStoresJoinedAsNonCreator' => $totalStoresJoinedAsNonCreator,
        ];
    }
}
