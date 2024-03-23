<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Address;
use App\Models\FriendGroup;
use Illuminate\Http\Response;
use App\Repositories\UserRepository;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Models\DeleteRequest;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\User\CreateUserRequest;
use App\Http\Requests\Models\User\UpdateUserRequest;
use App\Http\Requests\Models\User\ShowUserOrdersRequest;
use App\Http\Requests\Models\User\ShowUserStoresRequest;
use App\Http\Requests\Models\User\CreateFriendRequest;
use App\Http\Requests\Models\Address\CreateAddressRequest;
use App\Http\Requests\Models\Address\UpdateAddressRequest;
use App\Http\Requests\Auth\AcceptTermsAndConditionsRequest;
use App\Http\Requests\Models\User\ValidateCreateUserRequest;
use App\Http\Requests\Auth\ShowMobileVerificationCodeRequest;
use App\Http\Requests\Auth\VerifyMobileVerificationCodeRequest;
use App\Http\Requests\Auth\GenerateMobileVerificationCodeRequest;
use App\Http\Requests\Models\AiMessage\CreateAiMessageRequest;
use App\Http\Requests\Models\AiMessage\ShowAiMessagesRequest;
use App\Http\Requests\Models\AiMessage\UpdateAiMessageRequest;
use App\Http\Requests\Models\SmsAlertActivityAssociation\UpdateSmsAlertActivityAssociationRequest;
use App\Http\Requests\Models\FriendGroup\UpdateFriendGroupRequest;
use App\Http\Requests\Models\FriendGroup\CreateFriendGroupRequest;
use App\Http\Requests\Models\FriendGroup\DeleteFriendGroupsRequest;
use App\Http\Requests\Models\User\UpdateLastSelectedFriendsRequest;
use App\Http\Requests\Models\FriendGroup\ShowFriendGroupMembersRequest;
use App\Http\Requests\Models\FriendGroup\AddStoresFromFriendGroupRequest;
use App\Http\Requests\Models\FriendGroup\InviteFriendGroupMembersRequest;
use App\Http\Requests\Models\FriendGroup\RemoveStoresFromFriendGroupRequest;
use App\Http\Requests\Models\FriendGroup\RemoveFriendGroupMembersRequest;
use App\Http\Requests\Models\FriendGroup\UpdateLastSelectedFriendGroupsRequest;
use App\Http\Requests\Models\Store\CreateStoreRequest;
use App\Http\Requests\Models\Store\JoinStoreRequest;
use App\Http\Requests\Models\User\UpdateProfilePhotoRequest;
use App\Http\Requests\Models\User\CalculateAiAssistantSubscriptionRequest;
use App\Http\Requests\Models\User\CalculateSmsAlertTransactionAmount;
use App\Http\Requests\Models\User\CreateAiAssistantSubscriptionRequest;
use App\Http\Requests\Models\User\CreateSmsAlertTransactionRequest;
use App\Http\Requests\Models\User\GenerateAiAssistantPaymentShortcodeRequest;
use App\Http\Requests\Models\User\GenerateSmsAlertPaymentShortcodeRequest;
use App\Http\Requests\Models\User\RemoveFriendRequest as UserRemoveFriendRequest;
use App\Http\Requests\Models\User\SearchUserByMobileNumberRequest;
use App\Http\Requests\Models\User\ShowFriendGroupOrderFiltersRequest;
use App\Http\Requests\Models\User\ShowFriendGroupOrdersRequest;
use App\Http\Requests\Models\User\ShowUserReviewsRequest;
use App\Http\Requests\Models\User\ShowUserOrderFiltersRequest;
use App\Http\Requests\Models\User\ShowUserReviewFiltersRequest;
use App\Models\AiMessage;
use App\Models\SmsAlertActivityAssociation;
use App\Repositories\AiMessageRepository;
use App\Traits\Base\BaseTrait;
use Illuminate\Notifications\DatabaseNotification;

class UserController extends BaseController
{
    use BaseTrait;

    /**
     *  @var UserRepository
     */
    protected $repository;

    public function index()
    {
        return $this->prepareOutput($this->repository->get());
    }

    public function create(CreateUserRequest $request)
    {
        return $this->prepareOutput($this->repository->createUser($request), Response::HTTP_CREATED);
    }

    public function validateCreate(ValidateCreateUserRequest $_)
    {
        return $this->prepareOutput(null, Response::HTTP_OK);
    }

    public function searchUserByMobileNumber(SearchUserByMobileNumberRequest $request)
    {
        return $this->prepareOutput($this->repository->searchUserByMobileNumber($request));
    }

    public function show(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser()));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->updateUser($request));
    }

    public function confirmDelete(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->generateDeleteConfirmationCode());
    }

    public function delete(DeleteRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->delete());
    }

    public function showProfilePhoto(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showProfilePhoto());
    }

    public function updateProfilePhoto(UpdateProfilePhotoRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->updateProfilePhoto($request), Response::HTTP_CREATED);
    }

    public function deleteProfilePhoto(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->removeExistingProfilePhoto());
    }

    public function showTokens(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showTokens());
    }

    public function showTermsAndConditions(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showTermsAndConditions());
    }

    public function acceptTermsAndConditions(AcceptTermsAndConditionsRequest $acceptTermsAndConditionsRequest, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->acceptTermsAndConditions($acceptTermsAndConditionsRequest));
    }

    public function showMobileVerificationCode(ShowMobileVerificationCodeRequest $showMobileVerificationCodeRequest, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showMobileVerificationCode($showMobileVerificationCodeRequest));
    }

    public function verifyMobileVerificationCode(VerifyMobileVerificationCodeRequest $verifyMobileVerificationCodeRequest, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->verifyMobileVerificationCode($verifyMobileVerificationCodeRequest));
    }

    public function generateMobileVerificationCode(GenerateMobileVerificationCodeRequest $generateMobileVerificationCodeRequest, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->generateMobileVerificationCode($generateMobileVerificationCodeRequest));
    }

    public function logout(LogoutRequest $logoutRequest, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->logout($logoutRequest));
    }

    public function showNotificationFilters(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showNotificationFilters());
    }

    public function showNotifications(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showNotifications());
    }

    public function countNotifications(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->countNotifications());
    }

    public function markNotificationsAsRead(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->markNotificationsAsRead());
    }

    public function showNotification(User $user, DatabaseNotification $notification)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showNotification($notification));
    }

    public function markNotificationAsRead(User $user, DatabaseNotification $notification)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->markNotificationAsRead($notification));
    }






    public function showAddresses(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showAddresses());
    }

    public function createAddress(CreateAddressRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->createAddress($request), Response::HTTP_CREATED);
    }

    public function showAddress(User $user, Address $address)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showAddress($address));
    }

    public function updateAddress(UpdateAddressRequest $request, User $user, Address $address)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->updateAddress($request, $address));
    }

    public function deleteAddress(User $user, Address $address)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->deleteAddress($address));
    }





    public function showFriendAndFriendGroupFilters(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showFriendAndFriendGroupFilters());
    }

    public function showFriends(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showFriends());
    }

    public function removeFriends(UserRemoveFriendRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->removeFriends());
    }

    public function createFriends(CreateFriendRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->createFriends());
    }

    public function showLastSelectedFriend(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showLastSelectedFriend());
    }

    public function updateLastSelectedFriends(UpdateLastSelectedFriendsRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->updateLastSelectedFriends($request));
    }



    /// NOTE: Refer to the "RouteServiceProvider.php" file to observe hoe the $friendGroup is resolved.
    /// The logic is designed to support resolving the $friendGroup on the following routes:
    ///
    /// auth/user/friend-groups/{friend_group}
    //  users/user/friend-groups/{friend_group}

    public function showFriendGroupFilters(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showFriendGroupFilters());
    }

    public function showFriendGroups(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showFriendGroups());
    }

    public function showFriendGroup(User $user, FriendGroup $friendGroup)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showFriendGroup($friendGroup));
    }

    public function showFirstCreatedFriendGroup(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showFirstCreatedFriendGroup());
    }

    public function showLastSelectedFriendGroup(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showLastSelectedFriendGroup());
    }

    public function updateLastSelectedFriendGroups(UpdateLastSelectedFriendGroupsRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->updateLastSelectedFriendGroups($request));
    }

    public function createFriendGroup(CreateFriendGroupRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->createFriendGroup($request), Response::HTTP_CREATED);
    }

    public function updateFriendGroup(UpdateFriendGroupRequest $request, User $user, FriendGroup $friendGroup)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->updateFriendGroup($request, $friendGroup));
    }

    public function deleteFriendGroup(User $user, FriendGroup $friendGroup)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->deleteFriendGroup($friendGroup));
    }

    public function deleteManyFriendGroups(DeleteFriendGroupsRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->deleteManyFriendGroups($request));
    }

    public function inviteFriendGroupMembers(InviteFriendGroupMembersRequest $request, User $user, FriendGroup $friendGroup)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->inviteFriendGroupMembers($friendGroup, $request));
    }

    public function checkInvitationsToJoinFriendGroups(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->checkInvitationsToJoinFriendGroups());
    }

    public function acceptAllInvitationsToJoinFriendGroups(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->acceptAllInvitationsToJoinFriendGroups());
    }

    public function declineAllInvitationsToJoinFriendGroups(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->declineAllInvitationsToJoinFriendGroups());
    }

    public function acceptInvitationToJoinFriendGroup(User $user, FriendGroup $friendGroup)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->acceptInvitationToJoinFriendGroup($friendGroup));
    }

    public function declineInvitationToJoinFriendGroup(User $user, FriendGroup $friendGroup)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->declineInvitationToJoinFriendGroup($friendGroup));
    }

    public function removeFriendGroupMembers(RemoveFriendGroupMembersRequest $request, User $user, FriendGroup $friendGroup)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->removeFriendGroupMembers($friendGroup));
    }

    public function showFriendGroupMemberFilters(User $user, FriendGroup $friendGroup)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showFriendGroupMemberFilters($friendGroup));
    }

    public function showFriendGroupMembers(User $user, FriendGroup $friendGroup)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showFriendGroupMembers($friendGroup));
    }

    public function showFriendGroupStoreFilters(User $user, FriendGroup $friendGroup)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showFriendGroupStoreFilters($friendGroup));
    }

    public function showFriendGroupStores(User $user, FriendGroup $friendGroup)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showFriendGroupStores($friendGroup));
    }

    public function addFriendGroupStores(AddStoresFromFriendGroupRequest $request, User $user, FriendGroup $friendGroup)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->addFriendGroupStores($friendGroup, $request));
    }

    public function removeFriendGroupStores(RemoveStoresFromFriendGroupRequest $request, User $user, FriendGroup $friendGroup)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->removeFriendGroupStores($friendGroup, $request));
    }

    public function showFriendGroupOrderFilters(ShowFriendGroupOrderFiltersRequest $request, User $user, FriendGroup $friendGroup)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showFriendGroupOrderFilters($friendGroup));
    }

    public function showFriendGroupOrders(ShowFriendGroupOrdersRequest $request, User $user, FriendGroup $friendGroup)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showFriendGroupOrders($friendGroup));
    }


















    public function showOrderFilters(ShowUserOrderFiltersRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showUserOrderFilters());
    }

    public function showOrders(ShowUserOrdersRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showUserOrders());
    }

    public function showReviewFilters(ShowUserReviewFiltersRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showReviewFilters());
    }

    public function showReviews(ShowUserReviewsRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showReviews());
    }

    public function showFirstCreatedStore(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showUserFirstCreatedStore());
    }

    public function showStoreFilters(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showStoreFilters());
    }

    public function showStores(ShowUserStoresRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showStores());
    }

    public function createStore(CreateStoreRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->createStore($request), Response::HTTP_CREATED);
    }

    public function joinStore(JoinStoreRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->joinStore($request));
    }

    public function showAiAssistant(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showAiAssistant());
    }

    public function showAiAssistantSubscriptions(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showAiAssistantSubscriptions());
    }

    public function generateAiAssistantPaymentShortcode(GenerateAiAssistantPaymentShortcodeRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->generateAiAssistantPaymentShortcode($request));
    }

    public function createAiAssistantSubscription(CreateAiAssistantSubscriptionRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->createAiAssistantSubscription($request), Response::HTTP_CREATED);
    }

    public function calculateAiAccessSubscriptionAmount(CalculateAiAssistantSubscriptionRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->calculateAiAccessSubscriptionAmount($request));
    }

    public function showAiMessages(ShowAiMessagesRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showAiMessages($request));
    }

    public function createAiMessage(CreateAiMessageRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->createAiMessage($request), Response::HTTP_CREATED);
    }

    public function createAiMessageWhileStreaming(CreateAiMessageRequest $request, User $user)
    {
        //  Note: We do not need to return anything since we are streaming this request
        $this->setModel($this->chooseUser())->createAiMessageWhileStreaming($request);
    }

    public function showAiMessage(User $user, AiMessage $friendGroup)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showAiMessage($friendGroup));
    }

    public function updateAiMessage(UpdateAiMessageRequest $request, User $user, AiMessage $friendGroup)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->updateAiMessage($request, $friendGroup));
    }

    public function deleteAiMessage(User $user, AiMessage $friendGroup)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->deleteAiMessage($friendGroup));
    }





    public function showSmsAlert(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showSmsAlert());
    }

    public function generateSmsAlertPaymentShortcode(GenerateSmsAlertPaymentShortcodeRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->generateSmsAlertPaymentShortcode($request));
    }

    public function showSmsAlertTransactions(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showSmsAlertTransactions());
    }

    public function createSmsAlertTransaction(CreateSmsAlertTransactionRequest $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->createSmsAlertTransaction($request), Response::HTTP_CREATED);
    }

    public function calculateSmsAlertTransactionAmount(CalculateSmsAlertTransactionAmount $request, User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->calculateSmsAlertTransactionAmount($request));
    }

    public function updateSmsAlertActivityAssociation(UpdateSmsAlertActivityAssociationRequest $request, User $user, SmsAlertActivityAssociation $smsAlertActivityAssociation)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->updateSmsAlertActivityAssociation($smsAlertActivityAssociation, $request));
    }




    public function showResourceTotals(User $user)
    {
        return $this->prepareOutput($this->setModel($this->chooseUser())->showResourceTotals());
    }


}
