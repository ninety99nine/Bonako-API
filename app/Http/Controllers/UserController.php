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
use App\Http\Requests\Models\FriendGroup\RemoveStoresFromFriendGroupRequest;
use App\Http\Requests\Models\FriendGroup\RemoveFriendsFromFriendGroupRequest;
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
        return response($this->repository->setModel($this->chooseUser())->update($request)->transform(), Response::HTTP_OK);
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





    public function showFirstCreatedFriendGroup(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showUserFirstCreatedFriendGroup(), Response::HTTP_OK);
    }

    public function showFriendGroupFilters(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showFriendGroupFilters(), Response::HTTP_OK);
    }

    public function showFriendGroups(User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->showFriendGroups()->transform(), Response::HTTP_OK);
    }

    public function createFriendGroup(CreateFriendGroupRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->createFriendGroup($request), Response::HTTP_CREATED);
    }

    public function showFriendGroup(User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->showFriendGroup($friendGroup)->transform(), Response::HTTP_OK);
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

    public function showLastSelectedFriendGroup(User $user)
    {
        $response = $this->repository->setModel($this->chooseUser())->showLastSelectedFriendGroup();

        //  Transform the response if it's a non-null response
        return response($response == null ? null : $response->transform(), Response::HTTP_OK);
    }

    public function updateLastSelectedFriendGroups(UpdateLastSelectedFriendGroupsRequest $request, User $user)
    {
        return response($this->repository->setModel($this->chooseUser())->updateLastSelectedFriendGroups($request), Response::HTTP_OK);
    }

    public function showFriendGroupMembers(ShowFriendGroupMembersRequest $request, User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->showFriendGroupMembers($request, $friendGroup)->transform(), Response::HTTP_OK);
    }

    public function removeFriendGroupMembers(RemoveFriendsFromFriendGroupRequest $request, User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->removeFriendGroupMembers($request, $friendGroup), Response::HTTP_OK);
    }

    public function showFriendGroupStores(ShowUserStoresRequest $request, User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->showFriendGroupStores($friendGroup)->transform(), Response::HTTP_OK);
    }

    public function addFriendGroupStores(AddStoresFromFriendGroupRequest $request, User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->addFriendGroupStores($request, $friendGroup), Response::HTTP_OK);
    }

    public function removeFriendGroupStores(RemoveStoresFromFriendGroupRequest $request, User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($this->chooseUser())->removeFriendGroupStores($request, $friendGroup), Response::HTTP_OK);
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
        return response($this->repository->setModel($this->chooseUser())->createAiAssistantSubscription($request)->transform(), Response::HTTP_OK);
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
        $result = $this->repository->setModel($this->chooseUser())->createAiMessage($request);

        /**
         *  Provided that this is a non-stream request, we can return a response.
         *  This is because a stream response will return the data automatically
         *  and periodically using echo statements. There is no need for us to
         *  deliberately return anything. Therefore if the $result is an
         *  instance of AiMessageRepository, then we know that this is
         *  not a streamed response and we have an AI Message that we
         *  can return that has the entire content assistant message.
         */
        if($result instanceof AiMessageRepository) {

            $result = $result->transform();
            return response($result, Response::HTTP_CREATED);

        }
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
        return response($this->repository->setModel($this->chooseUser())->createSmsAlertTransaction($request)->transform(), Response::HTTP_OK);
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
