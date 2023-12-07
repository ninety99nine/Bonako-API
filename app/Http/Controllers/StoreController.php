<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Repositories\StoreRepository;
use App\Http\Requests\Models\DeleteRequest;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\Store\UpdateLogoRequest;
use App\Http\Requests\Models\Store\ShowStoresRequest;
use App\Http\Requests\Models\Store\ShowReviewsRequest;
use App\Http\Requests\Models\Store\UpdateStoreRequest;
use App\Http\Requests\Models\Store\CreateStoreRequest;
use App\Http\Requests\Models\Store\CreateAdvertRequest;
use App\Http\Requests\Models\Store\UpdateAdvertRequest;
use App\Http\Requests\Models\Review\CreateReviewRequest;
use App\Http\Requests\Models\Store\ShowFollowersRequest;
use App\Http\Requests\Models\Coupon\CreateCouponRequest;
use App\Http\Requests\Models\Store\InviteFollowersRequest;
use App\Http\Requests\Models\Store\ShowTeamMembersRequest;
use App\Http\Requests\Models\Product\CreateProductRequest;
use App\Http\Requests\Models\Product\UpdateAssignedStoreArrangementRequest;
use App\Http\Requests\Models\Store\InviteTeamMembersRequest;
use App\Http\Requests\Models\Store\RemoveTeamMembersRequest;
use App\Http\Requests\Models\ShoppingCart\ConvertCartRequest;
use App\Http\Requests\Models\ShoppingCart\InspectCartRequest;
use App\Http\Requests\Models\Store\AddStoreToFriendGroupsRequest;
use App\Http\Requests\Models\Store\CreateStoreSubscriptionRequest;
use App\Http\Requests\Models\Store\GeneratePaymentShortcodeRequest;
use App\Http\Requests\Models\Product\UpdateProductArrangementRequest;
use App\Http\Requests\Models\Product\UpdateProductVisibilityRequest;
use App\Http\Requests\Models\Store\CalculateStoreSubscriptionRequest;
use App\Http\Requests\Models\Store\RemoveStoreFromFriendGroupRequest;
use App\Http\Requests\Models\Store\UpdateTeamMemberPermissionsRequest;
use App\Http\Requests\Models\Store\ShowShoppingCartOrderForUsersRequest;
use App\Http\Requests\Models\Store\countShoppingCartOrderForUsersRequest;
use App\Http\Requests\Models\Store\DeleteAdvertRequest;
use App\Http\Requests\Models\Store\ShowCustomersRequest;
use App\Http\Requests\Models\Store\ShowStoreOrderFiltersRequest;
use App\Http\Requests\Models\Store\ShowStoreOrdersRequest;
use App\Http\Requests\Models\Store\ShowSubscriptionsRequest;
use App\Http\Requests\Models\Store\UpdateCoverPhotoRequest;
use App\Http\Requests\Models\Store\UpdateFollowingRequest;

class StoreController extends BaseController
{
    /**
     *  Explicit Route Model Binding
     *  Reference: https://laravel.com/docs/10.x/routing#customizing-the-resolution-logic
     *  ---------------------------------------------------------------------------------
     *
     *  The store and order are loaded on each controller method using the technique
     *  of explicit route model binding (Refer to the RouteServiceProvider.php file).
     *  This allows us to load the associated store and order with respect to the
     *  current authenticated user. This means that were possible, we can load
     *  the (user and store association) or (user and order association) pivot
     *  tables. This allows us to inspect the relationship that the user
     *  might have with respect to that specified store or order. Taking
     *  this into consideration, we can then access these associations
     *  to decide how to handle the given request or determine the
     *  right information to return based on these associations.
     *
     *  Although the $store model is loaded but not used particularly on some of
     *  these controller moethods, it still allows us to explicitly query the
     *  order with respect to that $store (see RouteServiceProvider.php file).
     *  In this case, while resolving the order we can access this store by
     *  using the request()->store convention since the store will now be
     *  accessible on the request.
     */

    /**
     *  @var StoreRepository
     */
    protected $repository;

    public function showStoreFilters(ShowStoresRequest $request)
    {
        return response($this->repository->showStoreFilters(), Response::HTTP_OK);
    }

    public function index(ShowStoresRequest $request)
    {
        return response($this->repository->showStores()->transform(), Response::HTTP_OK);
    }

    public function showBrandStores(ShowStoresRequest $request)
    {
        return response($this->repository->showBrandStores()->transform(), Response::HTTP_OK);
    }

    public function showInfluencerStores(ShowStoresRequest $request)
    {
        return response($this->repository->showInfluencerStores()->transform(), Response::HTTP_OK);
    }

    public function createStore(CreateStoreRequest $request)
    {
        return response($this->repository->createStore($request)->transform(), Response::HTTP_CREATED);
    }

    public function show(Store $store)
    {
        return response($this->repository->show($store)->transform(), Response::HTTP_OK);
    }

    public function update(UpdateStoreRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->updateStore($request)->transform(), Response::HTTP_OK);
    }

    public function confirmDelete(Store $store)
    {
        return response($this->repository->setModel($store)->generateDeleteConfirmationCode(), Response::HTTP_OK);
    }

    public function delete(DeleteRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->delete(), Response::HTTP_OK);
    }

    public function showAllTeamMemberPermissions()
    {
        return response($this->repository->showAllTeamMemberPermissions(), Response::HTTP_OK);
    }

    public function generatePaymentShortcode(GeneratePaymentShortcodeRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->generatePaymentShortcode($request)->transform(), Response::HTTP_OK);
    }

    public function showSupportedPaymentMethods(Store $store)
    {
        return response($this->repository->setModel($store)->showSupportedPaymentMethods()->transform(), Response::HTTP_OK);
    }

    public function showAvailablePaymentMethods(Store $store)
    {
        return response($this->repository->setModel($store)->showAvailablePaymentMethods()->transform(), Response::HTTP_OK);
    }

    public function showSharableContent(Store $store)
    {
        return response($this->repository->setModel($store)->showSharableContent(), Response::HTTP_OK);
    }

    public function showSharableContentChoices(Store $store)
    {
        return response($this->repository->setModel($store)->showSharableContentChoices(), Response::HTTP_OK);
    }

    public function createStoreAccessSubscription(CreateStoreSubscriptionRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->createStoreAccessSubscription($request)->transform(), Response::HTTP_CREATED);
    }

    public function createStoreAccessFakeSubscription(CreateStoreSubscriptionRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->createStoreAccessSubscription($request)->transform(), Response::HTTP_OK);
    }

    public function calculateStoreAccessSubscriptionAmount(CalculateStoreSubscriptionRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->calculateStoreAccessSubscriptionAmount($request), Response::HTTP_OK);
    }

    public function showMySubscriptions(ShowSubscriptionsRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->showMySubscriptions($request)->transform(), Response::HTTP_OK);
    }

    public function showVisitShortcode(Store $store)
    {
        return response($this->repository->setModel($store)->showVisitShortcode()->transform(), Response::HTTP_OK);
    }

    public function showCouponFilters(Store $store)
    {
        return response($this->repository->setModel($store)->showCouponFilters(), Response::HTTP_OK);
    }

    public function showCoupons(Store $store)
    {
        return response($this->repository->setModel($store)->showCoupons()->transform(), Response::HTTP_OK);
    }

    public function createCoupon(CreateCouponRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->createCoupon($request)->transform(), Response::HTTP_CREATED);
    }

    public function showProductFilters(Store $store)
    {
        return response($this->repository->setModel($store)->showProductFilters(), Response::HTTP_OK);
    }

    public function showProducts(Store $store)
    {
        return response($this->repository->setModel($store)->showProducts()->transform(), Response::HTTP_OK);
    }

    public function createProduct(CreateProductRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->createProduct($request)->transform(), Response::HTTP_CREATED);
    }

    public function updateProductVisibility(UpdateProductVisibilityRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->updateProductVisibility($request), Response::HTTP_OK);
    }

    public function updateProductArrangement(UpdateProductArrangementRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->updateProductArrangement($request), Response::HTTP_OK);
    }

    public function showOrderFilters(ShowStoreOrderFiltersRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->showOrderFilters(), Response::HTTP_OK);
    }

    public function showOrders(ShowStoreOrdersRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->showOrders()->transform(), Response::HTTP_OK);
    }

    public function showLogo(Store $store)
    {
        return response($this->repository->setModel($store)->showLogo(), Response::HTTP_OK);
    }

    public function updateLogo(UpdateLogoRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->updateLogo($request), Response::HTTP_CREATED);
    }

    public function deleteLogo(Store $store)
    {
        return response($this->repository->setModel($store)->removeExistingLogo(), Response::HTTP_OK);
    }

    public function showAdverts(Store $store)
    {
        return response($this->repository->setModel($store)->showAdverts(), Response::HTTP_OK);
    }

    public function createAdvert(CreateAdvertRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->createAdvert($request), Response::HTTP_CREATED);
    }

    public function updateAdvert(UpdateAdvertRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->updateAdvert($request), Response::HTTP_CREATED);
    }

    public function deleteAdvert(DeleteAdvertRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->deleteAdvert($request), Response::HTTP_OK);
    }

    public function showCoverPhoto(Store $store)
    {
        return response($this->repository->setModel($store)->showCoverPhoto(), Response::HTTP_OK);
    }

    public function updateCoverPhoto(UpdateCoverPhotoRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->updateCoverPhoto($request), Response::HTTP_CREATED);
    }

    public function deleteCoverPhoto(Store $store)
    {
        return response($this->repository->setModel($store)->removeExistingCoverPhoto(), Response::HTTP_OK);
    }

    public function showReviewFilters(Store $store)
    {
        return response($this->repository->setModel($store)->showReviewFilters(), Response::HTTP_OK);
    }

    public function showReviews(ShowReviewsRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->showReviews()->transform(), Response::HTTP_OK);
    }

    public function showReviewRatingOptions(Store $store)
    {
        return response($this->repository->setModel($store)->showReviewRatingOptions(), Response::HTTP_OK);
    }

    public function createReview(CreateReviewRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->createReview($request)->transform(), Response::HTTP_CREATED);
    }

    public function checkInvitationsToFollow()
    {
        return response($this->repository->checkInvitationsToFollow(), Response::HTTP_OK);
    }

    public function acceptAllInvitationsToFollow()
    {
        return response($this->repository->acceptAllInvitationsToFollow(), Response::HTTP_OK);
    }

    public function declineAllInvitationsToFollow()
    {
        return response($this->repository->declineAllInvitationsToFollow(), Response::HTTP_OK);
    }

    public function showFollowerFilters(Request $request, Store $store)
    {
        return response($this->repository->setModel($store)->showFollowerFilters(), Response::HTTP_OK);
    }

    public function showFollowers(ShowFollowersRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->showFollowers()->transform(), Response::HTTP_OK);
    }

    public function inviteFollowers(InviteFollowersRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->inviteFollowers(), Response::HTTP_OK);
    }

    public function acceptInvitationToFollow(Store $store)
    {
        return response($this->repository->setModel($store)->acceptInvitationToFollow(), Response::HTTP_OK);
    }

    public function declineInvitationToFollow(Store $store)
    {
        return response($this->repository->setModel($store)->declineInvitationToFollow(), Response::HTTP_OK);
    }

    public function showFollowing(Store $store)
    {
        return response($this->repository->setModel($store)->showFollowing(auth()->user()), Response::HTTP_OK);
    }

    public function updateFollowing(UpdateFollowingRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->updateFollowing($request, auth()->user()), Response::HTTP_OK);
    }

    public function showTeamMemberFilters(Request $request, Store $store)
    {
        return response($this->repository->setModel($store)->showTeamMemberFilters(), Response::HTTP_OK);
    }

    public function showMyPermissions(Store $store)
    {
        return response($this->repository->setModel($store)->showTeamMemberPermissions(auth()->user()), Response::HTTP_OK);
    }

    public function showTeamMembers(ShowTeamMembersRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->showTeamMembers()->transform(), Response::HTTP_OK);
    }

    /**
     *  Route model binding will search for the store relationship based on the plural
     *  form of the route parameter name ($teamMember). This plural form in this case
     *  is the (teamMembers) relationship which can be used to retrieve the User
     *  model matching the query. This outcome is the following:
     *
     *  $store->teamMembers()->where('id', $teamMember); where $teamMember
     *  is the integer that is being passed on that route input.
     *
     *  Learn More: https://laravel.com/docs/9.x/routing#implicit-model-binding-scoping
     */
    public function showTeamMember(Store $store, User $teamMember)
    {
        return response($this->repository->setModel($store)->showTeamMember($teamMember)->transform(), Response::HTTP_OK);
    }

    public function showTeamMemberPermissions(Store $store, User $teamMember)
    {
        return response($this->repository->setModel($store)->showTeamMemberPermissions($teamMember), Response::HTTP_OK);
    }

    public function updateTeamMemberPermissions(UpdateTeamMemberPermissionsRequest $request, Store $store, User $teamMember)
    {
        return response($this->repository->setModel($store)->updateTeamMemberPermissions($teamMember), Response::HTTP_OK);
    }

    public function inviteTeamMembers(InviteTeamMembersRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->inviteTeamMembers(), Response::HTTP_OK);
    }

    /**
     *  Removing Team Members
     *
     *  Note that we use the same route to remove one or multiple users by specifying the mobile
     *  numbers to be removed on this request. We do not remove a single user by specifying that
     *  user's id on the route since we should be able to remove guest users who share the same
     *  guest account with a user id equal to "0". If we attempted to remove a specific user by
     *  specifying that user's id, we wouldn't know exactly which user that would be if the
     *  user is a guest user e.g
     *
     *  The following route makes sense (remove user with id = 1 from store id = 1)
     *
     *  http://127.0.0.1:8000/api/v1/stores/1/team-members/1/remove
     *
     *  But this route does not make sense
     *
     *  http://127.0.0.1:8000/api/v1/stores/1/team-members/0/remove
     *
     *  This route does not make sense (remove user with id = 0 from store id = 1) because we know
     *  that user with id = 0 is a shared guest account until those users one day create their own
     *  personal accounts. Who we couldn't possibly know exatly which account to remove from being
     *  a team member of this store. Considering this case, we support removing one or multiple
     *  users by specifying the mobile numbers to be removed on this request
     */
    public function removeTeamMembers(RemoveTeamMembersRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->removeTeamMembers($request), Response::HTTP_OK);
    }

    public function checkInvitationsToJoinTeam()
    {
        return response($this->repository->checkInvitationsToJoinTeam(), Response::HTTP_OK);
    }

    public function acceptAllInvitationsToJoinTeam()
    {
        return response($this->repository->acceptAllInvitationsToJoinTeam(), Response::HTTP_OK);
    }

    public function declineAllInvitationsToJoinTeam()
    {
        return response($this->repository->declineAllInvitationsToJoinTeam(), Response::HTTP_OK);
    }

    public function acceptInvitationToJoinTeam(Store $store)
    {
        return response($this->repository->setModel($store)->acceptInvitationToJoinTeam(), Response::HTTP_OK);
    }

    public function declineInvitationToJoinTeam(Store $store)
    {
        return response($this->repository->setModel($store)->declineInvitationToJoinTeam(), Response::HTTP_OK);
    }

    public function showCustomerFilters(Request $request, Store $store)
    {
        return response($this->repository->setModel($store)->showCustomerFilters(), Response::HTTP_OK);
    }

    public function showCustomers(ShowCustomersRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->showCustomers()->transform(), Response::HTTP_OK);
    }

    /**
     *  Route model binding will search for the store relationship based on the plural
     *  form of the route parameter name ($customer). This plural form in this case
     *  is the (customers) relationship which can be used to retrieve the User
     *  model matching the query. This outcome is the following:
     *
     *  $store->customers()->where('id', $customer); where $customer
     *  is the integer that is being passed on that route input.
     *
     *  Learn More: https://laravel.com/docs/9.x/routing#implicit-model-binding-scoping
     */
    public function showCustomer(Store $store, User $customer)
    {
        return response($this->repository->setModel($store)->showCustomer($customer)->transform(), Response::HTTP_OK);
    }

    public function addStoreToFriendGroups(AddStoreToFriendGroupsRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->addStoreToFriendGroups(), Response::HTTP_OK);
    }

    public function removeStoreFromFriendGroup(RemoveStoreFromFriendGroupRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->removeStoreFromFriendGroup(), Response::HTTP_OK);
    }

    public function addStoreToBrandStores(Store $store)
    {
        return response($this->repository->setModel($store)->addStoreToBrandStores(), Response::HTTP_OK);
    }

    public function removeStoreFromBrandStores(Store $store)
    {
        return response($this->repository->setModel($store)->removeStoreFromBrandStores(), Response::HTTP_OK);
    }

    public function addOrRemoveStoreFromBrandStores(Store $store)
    {
        return response($this->repository->setModel($store)->addOrRemoveStoreFromBrandStores(), Response::HTTP_OK);
    }

    public function addStoreToInfluencerStores(Store $store)
    {
        return response($this->repository->setModel($store)->addStoreToInfluencerStores(), Response::HTTP_OK);
    }

    public function removeStoreFromInfluencerStores(Store $store)
    {
        return response($this->repository->setModel($store)->removeStoreFromInfluencerStores(), Response::HTTP_OK);
    }

    public function addOrRemoveStoreFromInfluencerStores(Store $store)
    {
        return response($this->repository->setModel($store)->addOrRemoveStoreFromInfluencerStores(), Response::HTTP_OK);
    }





    public function updateAssignedStoreArrangement(UpdateAssignedStoreArrangementRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->updateAssignedStoreArrangement($request), Response::HTTP_OK);
    }


    public function addStoreToAssignedStores(Store $store)
    {
        return response($this->repository->setModel($store)->addStoreToAssignedStores(), Response::HTTP_OK);
    }

    public function removeStoreFromAssignedStores(Store $store)
    {
        return response($this->repository->setModel($store)->removeStoreFromAssignedStores(), Response::HTTP_OK);
    }

    public function addOrRemoveStoreFromAssignedStores(Store $store)
    {
        return response($this->repository->setModel($store)->addOrRemoveStoreFromAssignedStores(), Response::HTTP_OK);
    }





    public function showShoppingCartOrderForOptions(Store $store)
    {
        return response($this->repository->setModel($store)->showShoppingCartOrderForOptions(), Response::HTTP_OK);
    }

    public function showShoppingCartOrderForUsers(ShowShoppingCartOrderForUsersRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->showShoppingCartOrderForUsers($request)->transform(), Response::HTTP_OK);
    }

    public function countShoppingCartOrderForUsers(countShoppingCartOrderForUsersRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->countShoppingCartOrderForUsers($request), Response::HTTP_OK);
    }

    public function inspectShoppingCart(InspectCartRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->inspectShoppingCart()->transform(), Response::HTTP_OK);
    }

    public function convertShoppingCart(ConvertCartRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->convertShoppingCart()->transform(), Response::HTTP_CREATED);
    }
}
