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
        return $this->prepareOutput($this->repository->showStoreFilters());
    }

    public function index(ShowStoresRequest $request)
    {
        return $this->prepareOutput($this->repository->showStores());
    }

    public function showBrandStores(ShowStoresRequest $request)
    {
        return $this->prepareOutput($this->repository->showBrandStores());
    }

    public function showInfluencerStores(ShowStoresRequest $request)
    {
        return $this->prepareOutput($this->repository->showInfluencerStores());
    }

    public function createStore(CreateStoreRequest $request)
    {
        return $this->prepareOutput($this->repository->createStore($request), Response::HTTP_CREATED);
    }

    public function show(Store $store)
    {
        return $this->prepareOutput($this->repository->show($store));
    }

    public function update(UpdateStoreRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->updateStore($request));
    }

    public function confirmDelete(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->generateDeleteConfirmationCode());
    }

    public function delete(DeleteRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->deleteStore());
    }

    public function showAllTeamMemberPermissions()
    {
        return $this->prepareOutput($this->repository->showAllTeamMemberPermissions());
    }

    public function generatePaymentShortcode(GeneratePaymentShortcodeRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->generatePaymentShortcode($request));
    }

    public function showSupportedPaymentMethods(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showSupportedPaymentMethods());
    }

    public function showAvailablePaymentMethods(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showAvailablePaymentMethods());
    }

    public function showAvailableDepositPercentages(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showAvailableDepositPercentages());
    }

    public function showAvailableInstallmentPercentages(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showAvailableInstallmentPercentages());
    }

    public function showSharableContent(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showSharableContent());
    }

    public function showSharableContentChoices(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showSharableContentChoices());
    }

    public function createStoreAccessSubscription(CreateStoreSubscriptionRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->createStoreAccessSubscription($request), Response::HTTP_CREATED);
    }

    public function createStoreAccessFakeSubscription(CreateStoreSubscriptionRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->createStoreAccessSubscription($request), Response::HTTP_CREATED);
    }

    public function calculateStoreAccessSubscriptionAmount(CalculateStoreSubscriptionRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->calculateStoreAccessSubscriptionAmount($request));
    }

    public function showMySubscriptions(ShowSubscriptionsRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showMySubscriptions($request));
    }

    public function showTransactions(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showTransactions());
    }

    public function showVisitShortcode(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showVisitShortcode());
    }

    public function showCouponFilters(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showCouponFilters());
    }

    public function showCoupons(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showCoupons());
    }

    public function createCoupon(CreateCouponRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->createCoupon($request), Response::HTTP_CREATED);
    }

    public function showProductFilters(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showProductFilters());
    }

    public function showProducts(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showProducts());
    }

    public function createProduct(CreateProductRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->createProduct($request), Response::HTTP_CREATED);
    }

    public function updateProductVisibility(UpdateProductVisibilityRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->updateProductVisibility($request));
    }

    public function updateProductArrangement(UpdateProductArrangementRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->updateProductArrangement($request));
    }

    public function showOrderFilters(ShowStoreOrderFiltersRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showOrderFilters());
    }

    public function showOrders(ShowStoreOrdersRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showOrders());
    }

    public function showLogo(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showLogo());
    }

    public function updateLogo(UpdateLogoRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->updateLogo($request), Response::HTTP_CREATED);
    }

    public function deleteLogo(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->removeExistingLogo());
    }

    public function showAdverts(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showAdverts());
    }

    public function createAdvert(CreateAdvertRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->createAdvert($request), Response::HTTP_CREATED);
    }

    public function updateAdvert(UpdateAdvertRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->updateAdvert($request), Response::HTTP_CREATED);
    }

    public function deleteAdvert(DeleteAdvertRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->deleteAdvert($request));
    }

    public function showCoverPhoto(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showCoverPhoto());
    }

    public function updateCoverPhoto(UpdateCoverPhotoRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->updateCoverPhoto($request), Response::HTTP_CREATED);
    }

    public function deleteCoverPhoto(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->removeExistingCoverPhoto());
    }

    public function showQuickStartGuide(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showQuickStartGuide());
    }

    public function showReviewFilters(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showReviewFilters());
    }

    public function showReviews(ShowReviewsRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showReviews());
    }

    public function showReviewRatingOptions(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showReviewRatingOptions());
    }

    public function createReview(CreateReviewRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->createReview($request), Response::HTTP_CREATED);
    }

    public function checkInvitationsToFollow()
    {
        return $this->prepareOutput($this->repository->checkInvitationsToFollow());
    }

    public function acceptAllInvitationsToFollow()
    {
        return $this->prepareOutput($this->repository->acceptAllInvitationsToFollow());
    }

    public function declineAllInvitationsToFollow()
    {
        return $this->prepareOutput($this->repository->declineAllInvitationsToFollow());
    }

    public function showFollowerFilters(Request $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showFollowerFilters());
    }

    public function showFollowers(ShowFollowersRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showFollowers());
    }

    public function inviteFollowers(InviteFollowersRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->inviteFollowers());
    }

    public function acceptInvitationToFollow(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->acceptInvitationToFollow());
    }

    public function declineInvitationToFollow(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->declineInvitationToFollow());
    }

    public function showFollowing(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showFollowing(request()->auth_user));
    }

    public function updateFollowing(UpdateFollowingRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->updateFollowing($request, request()->auth_user));
    }

    public function showTeamMemberFilters(Request $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showTeamMemberFilters());
    }

    public function showMyPermissions(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showTeamMemberPermissions(request()->auth_user));
    }

    public function showTeamMembers(ShowTeamMembersRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showTeamMembers());
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
        return $this->prepareOutput($this->setModel($store)->showTeamMember($teamMember));
    }

    public function showTeamMemberPermissions(Store $store, User $teamMember)
    {
        return $this->prepareOutput($this->setModel($store)->showTeamMemberPermissions($teamMember));
    }

    public function updateTeamMemberPermissions(UpdateTeamMemberPermissionsRequest $request, Store $store, User $teamMember)
    {
        return $this->prepareOutput($this->setModel($store)->updateTeamMemberPermissions($teamMember));
    }

    public function inviteTeamMembers(InviteTeamMembersRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->inviteTeamMembers());
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
        return $this->prepareOutput($this->setModel($store)->removeTeamMembers($request));
    }

    public function checkInvitationsToJoinTeam()
    {
        return $this->prepareOutput($this->repository->checkInvitationsToJoinTeam());
    }

    public function acceptAllInvitationsToJoinTeam()
    {
        return $this->prepareOutput($this->repository->acceptAllInvitationsToJoinTeam());
    }

    public function declineAllInvitationsToJoinTeam()
    {
        return $this->prepareOutput($this->repository->declineAllInvitationsToJoinTeam());
    }

    public function acceptInvitationToJoinTeam(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->acceptInvitationToJoinTeam());
    }

    public function declineInvitationToJoinTeam(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->declineInvitationToJoinTeam());
    }

    public function showCustomerFilters(Request $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showCustomerFilters());
    }

    public function showCustomers(ShowCustomersRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showCustomers());
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
        return $this->prepareOutput($this->setModel($store)->showCustomer($customer));
    }

    public function addStoreToFriendGroups(AddStoreToFriendGroupsRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->addStoreToFriendGroups());
    }

    public function removeStoreFromFriendGroup(RemoveStoreFromFriendGroupRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->removeStoreFromFriendGroup());
    }

    public function addStoreToBrandStores(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->addStoreToBrandStores());
    }

    public function removeStoreFromBrandStores(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->removeStoreFromBrandStores());
    }

    public function addOrRemoveStoreFromBrandStores(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->addOrRemoveStoreFromBrandStores());
    }

    public function addStoreToInfluencerStores(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->addStoreToInfluencerStores());
    }

    public function removeStoreFromInfluencerStores(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->removeStoreFromInfluencerStores());
    }

    public function addOrRemoveStoreFromInfluencerStores(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->addOrRemoveStoreFromInfluencerStores());
    }





    public function updateAssignedStoreArrangement(UpdateAssignedStoreArrangementRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->updateAssignedStoreArrangement($request));
    }


    public function addStoreToAssignedStores(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->addStoreToAssignedStores());
    }

    public function removeStoreFromAssignedStores(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->removeStoreFromAssignedStores());
    }

    public function addOrRemoveStoreFromAssignedStores(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->addOrRemoveStoreFromAssignedStores());
    }





    public function showShoppingCartOrderForOptions(Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showShoppingCartOrderForOptions());
    }

    public function showShoppingCartOrderForUsers(ShowShoppingCartOrderForUsersRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->showShoppingCartOrderForUsers($request));
    }

    public function countShoppingCartOrderForUsers(countShoppingCartOrderForUsersRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->countShoppingCartOrderForUsers($request));
    }

    public function inspectShoppingCart(InspectCartRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->inspectShoppingCart());
    }

    public function convertShoppingCart(ConvertCartRequest $request, Store $store)
    {
        return $this->prepareOutput($this->setModel($store)->convertShoppingCart(), Response::HTTP_CREATED);
    }
}
