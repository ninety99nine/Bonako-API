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
        return response($this->repository->get()->transform(), Response::HTTP_OK);
    }

    public function create(CreateUserRequest $request)
    {
        return response($this->repository->create($request), Response::HTTP_CREATED);
    }

    public function validateCreate(ValidateCreateUserRequest $_)
    {
        return response(null, Response::HTTP_OK);
    }

    public function searchUserByMobileNumber(SearchUserByMobileNumberRequest $request)
    {
        return response($this->repository->searchUserByMobileNumber($request), Response::HTTP_OK);
    }

    public function show(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->transform(), Response::HTTP_OK);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->updateUser($request)->transform(), Response::HTTP_OK);
    }

    public function confirmDelete(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->generateDeleteConfirmationCode(), Response::HTTP_OK);
    }

    public function delete(DeleteRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->delete(), Response::HTTP_OK);
    }

    public function showProfilePhoto(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showProfilePhoto(), Response::HTTP_OK);
    }

    public function updateProfilePhoto(UpdateProfilePhotoRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->updateProfilePhoto($request), Response::HTTP_CREATED);
    }

    public function deleteProfilePhoto(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->removeExistingProfilePhoto(), Response::HTTP_OK);
    }

    public function showTokens(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showTokens(), Response::HTTP_OK);
    }

    public function showTermsAndConditions(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showTermsAndConditions(), Response::HTTP_OK);
    }

    public function acceptTermsAndConditions(AcceptTermsAndConditionsRequest $acceptTermsAndConditionsRequest, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->acceptTermsAndConditions($acceptTermsAndConditionsRequest), Response::HTTP_OK);
    }

    public function showMobileVerificationCode(ShowMobileVerificationCodeRequest $showMobileVerificationCodeRequest, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showMobileVerificationCode($showMobileVerificationCodeRequest), Response::HTTP_OK);
    }

    public function verifyMobileVerificationCode(VerifyMobileVerificationCodeRequest $verifyMobileVerificationCodeRequest, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->verifyMobileVerificationCode($verifyMobileVerificationCodeRequest), Response::HTTP_OK);
    }

    public function generateMobileVerificationCode(GenerateMobileVerificationCodeRequest $generateMobileVerificationCodeRequest, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->generateMobileVerificationCode($generateMobileVerificationCodeRequest), Response::HTTP_OK);
    }

    public function logout(LogoutRequest $logoutRequest, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->logout($logoutRequest), Response::HTTP_OK);
    }

    public function showNotificationFilters(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showNotificationFilters(), Response::HTTP_OK);
    }

    public function showNotifications(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showNotifications()->transform(), Response::HTTP_OK);
    }

    public function countNotifications(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->countNotifications(), Response::HTTP_OK);
    }

    public function markNotificationsAsRead(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->markNotificationsAsRead(), Response::HTTP_OK);
    }

    public function showNotification(User $user, DatabaseNotification $notification)
    {
        return response($this->repository->setModel($this->chooseUser())->showNotification($notification)->transform(), Response::HTTP_OK);
    }

    public function markNotificationAsRead(User $user, DatabaseNotification $notification)
    {
        return response($this->repository->setModel($this->chooseUser())->markNotificationAsRead($notification), Response::HTTP_OK);
    }






    public function showAddresses(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showAddresses()->transform(), Response::HTTP_OK);
    }

    public function createAddress(CreateAddressRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->createAddress($request)->transform(), Response::HTTP_CREATED);
    }

    public function showAddress(User $user, Address $address)
    {
        return response($this->repository->setModel($this->chooseUser())->showAddress($address)->transform(), Response::HTTP_OK);
    }

    public function updateAddress(UpdateAddressRequest $request, User $user, Address $address)
    {
        return response($this->repository->setModel($this->chooseUser())->updateAddress($request, $address)->transform(), Response::HTTP_OK);
    }

    public function deleteAddress(User $user, Address $address)
    {
        return response($this->repository->setModel($this->chooseUser())->deleteAddress($address), Response::HTTP_OK);
    }





    public function showFriendAndFriendGroupFilters(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showFriendAndFriendGroupFilters(), Response::HTTP_OK);
    }

    public function showFriends(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showFriends()->transform(), Response::HTTP_OK);
    }

    public function removeFriends(UserRemoveFriendRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->removeFriends(), Response::HTTP_OK);
    }

    public function createFriends(CreateFriendRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->createFriends(), Response::HTTP_OK);
    }

    public function showLastSelectedFriend(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showLastSelectedFriend()->transform(), Response::HTTP_OK);
    }

    public function updateLastSelectedFriends(UpdateLastSelectedFriendsRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->updateLastSelectedFriends($request), Response::HTTP_OK);
    }



    /// NOTE: Refer to the "RouteServiceProvider.php" file to observe hoe the $friendGroup is resolved.
    /// The logic is designed to support resolving the $friendGroup on the following routes:
    ///
    /// auth/user/friend-groups/{friend_group}
    //  users/user/friend-groups/{friend_group}

    public function showFriendGroupFilters(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showFriendGroupFilters(), Response::HTTP_OK);
    }

    public function showFriendGroups(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showFriendGroups()->transform(), Response::HTTP_OK);
    }

    public function showFriendGroup(User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->showFriendGroup($friendGroup)->transform(), Response::HTTP_OK);
    }

    public function showFirstCreatedFriendGroup(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showFirstCreatedFriendGroup(), Response::HTTP_OK);
    }

    public function showLastSelectedFriendGroup(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showLastSelectedFriendGroup(), Response::HTTP_OK);
    }

    public function updateLastSelectedFriendGroups(UpdateLastSelectedFriendGroupsRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->updateLastSelectedFriendGroups($request), Response::HTTP_OK);
    }

    public function createFriendGroup(CreateFriendGroupRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->createFriendGroup($request), Response::HTTP_CREATED);
    }

    public function updateFriendGroup(UpdateFriendGroupRequest $request, User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->updateFriendGroup($request, $friendGroup), Response::HTTP_OK);
    }

    public function deleteFriendGroup(User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->deleteFriendGroup($friendGroup), Response::HTTP_OK);
    }

    public function deleteManyFriendGroups(DeleteFriendGroupsRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->deleteManyFriendGroups($request), Response::HTTP_OK);
    }

    public function inviteFriendGroupMembers(InviteFriendGroupMembersRequest $request, User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->inviteFriendGroupMembers($friendGroup, $request), Response::HTTP_OK);
    }

    public function checkInvitationsToJoinFriendGroups(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->checkInvitationsToJoinFriendGroups(), Response::HTTP_OK);
    }

    public function acceptAllInvitationsToJoinFriendGroups(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->acceptAllInvitationsToJoinFriendGroups(), Response::HTTP_OK);
    }

    public function declineAllInvitationsToJoinFriendGroups(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->declineAllInvitationsToJoinFriendGroups(), Response::HTTP_OK);
    }

    public function acceptInvitationToJoinFriendGroup(User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->acceptInvitationToJoinFriendGroup($friendGroup), Response::HTTP_OK);
    }

    public function declineInvitationToJoinFriendGroup(User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->declineInvitationToJoinFriendGroup($friendGroup), Response::HTTP_OK);
    }

    public function removeFriendGroupMembers(RemoveFriendGroupMembersRequest $request, User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->removeFriendGroupMembers($friendGroup), Response::HTTP_OK);
    }

    public function showFriendGroupMemberFilters(User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->showFriendGroupMemberFilters($friendGroup), Response::HTTP_OK);
    }

    public function showFriendGroupMembers(User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->showFriendGroupMembers($friendGroup)->transform(), Response::HTTP_OK);
    }

    public function showFriendGroupStoreFilters(User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->showFriendGroupStoreFilters($friendGroup), Response::HTTP_OK);
    }

    public function showFriendGroupStores(User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->showFriendGroupStores($friendGroup)->transform(), Response::HTTP_OK);
    }

    public function addFriendGroupStores(AddStoresFromFriendGroupRequest $request, User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->addFriendGroupStores($friendGroup, $request), Response::HTTP_OK);
    }

    public function removeFriendGroupStores(RemoveStoresFromFriendGroupRequest $request, User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->removeFriendGroupStores($friendGroup, $request), Response::HTTP_OK);
    }

    public function showFriendGroupOrderFilters(ShowFriendGroupOrderFiltersRequest $request, User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->showFriendGroupOrderFilters($friendGroup), Response::HTTP_OK);
    }

    public function showFriendGroupOrders(ShowFriendGroupOrdersRequest $request, User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->showFriendGroupOrders($friendGroup)->transform(), Response::HTTP_OK);
    }


















    public function showOrderFilters(ShowUserOrderFiltersRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showUserOrderFilters(), Response::HTTP_OK);
    }

    public function showOrders(ShowUserOrdersRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showUserOrders()->transform(), Response::HTTP_OK);
    }

    public function showReviewFilters(ShowUserReviewFiltersRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showReviewFilters(), Response::HTTP_OK);
    }

    public function showReviews(ShowUserReviewsRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showReviews()->transform(), Response::HTTP_OK);
    }

    public function showFirstCreatedStore(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showUserFirstCreatedStore(), Response::HTTP_OK);
    }

    public function showStoreFilters(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showStoreFilters(), Response::HTTP_OK);
    }

    public function showStores(ShowUserStoresRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showStores()->transform(), Response::HTTP_OK);
    }

    public function createStore(CreateStoreRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->createStore($request)->transform(), Response::HTTP_OK);
    }

    public function joinStore(JoinStoreRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->joinStore($request), Response::HTTP_OK);
    }

    public function showAiAssistant(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showAiAssistant()->transform(), Response::HTTP_OK);
    }

    public function showAiAssistantSubscriptions(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showAiAssistantSubscriptions()->transform(), Response::HTTP_OK);
    }

    public function generateAiAssistantPaymentShortcode(GenerateAiAssistantPaymentShortcodeRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->generateAiAssistantPaymentShortcode($request)->transform(), Response::HTTP_OK);
    }

    public function createAiAssistantSubscription(CreateAiAssistantSubscriptionRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->createAiAssistantSubscription($request)->transform(), Response::HTTP_CREATED);
    }

    public function calculateAiAccessSubscriptionAmount(CalculateAiAssistantSubscriptionRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->calculateAiAccessSubscriptionAmount($request), Response::HTTP_OK);
    }

    public function showAiMessages(ShowAiMessagesRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showAiMessages($request)->transform(), Response::HTTP_OK);
    }

    public function createAiMessage(CreateAiMessageRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->createAiMessage($request)->transform(), Response::HTTP_CREATED);
    }

    public function createAiMessageWhileStreaming(CreateAiMessageRequest $request, User $user)
    {
        //  Note: We do not need to return anything since we are streaming this request
        $this->repository->setModel($this->chooseUser())->createAiMessageWhileStreaming($request);
    }

    public function showAiMessage(User $user, AiMessage $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->showAiMessage($friendGroup)->transform(), Response::HTTP_OK);
    }

    public function updateAiMessage(UpdateAiMessageRequest $request, User $user, AiMessage $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->updateAiMessage($request, $friendGroup), Response::HTTP_OK);
    }

    public function deleteAiMessage(User $user, AiMessage $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->deleteAiMessage($friendGroup), Response::HTTP_OK);
    }





    public function showSmsAlert(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showSmsAlert()->transform(), Response::HTTP_OK);
    }

    public function generateSmsAlertPaymentShortcode(GenerateSmsAlertPaymentShortcodeRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->generateSmsAlertPaymentShortcode($request)->transform(), Response::HTTP_OK);
    }

    public function showSmsAlertTransactions(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showSmsAlertTransactions()->transform(), Response::HTTP_OK);
    }

    public function createSmsAlertTransaction(CreateSmsAlertTransactionRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->createSmsAlertTransaction($request)->transform(), Response::HTTP_CREATED);
    }

    public function calculateSmsAlertTransactionAmount(CalculateSmsAlertTransactionAmount $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->calculateSmsAlertTransactionAmount($request), Response::HTTP_OK);
    }

    public function updateSmsAlertActivityAssociation(UpdateSmsAlertActivityAssociationRequest $request, User $user, SmsAlertActivityAssociation $smsAlertActivityAssociation)
    {
        return response($this->repository->setModel($this->chooseUser())->updateSmsAlertActivityAssociation($smsAlertActivityAssociation, $request)->transform(), Response::HTTP_OK);
    }




    public function showResourceTotals(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showResourceTotals(), Response::HTTP_OK);
    }


}
