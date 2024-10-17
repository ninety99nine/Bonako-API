<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Enums\UserFriendGroupRole;
use App\Repositories\FriendGroupRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\FriendGroup\ShowFriendGroupsRequest;
use App\Http\Requests\Models\FriendGroup\CreateFriendGroupRequest;
use App\Http\Requests\Models\FriendGroup\UpdateFriendGroupRequest;
use App\Http\Requests\Models\FriendGroup\RemoveFriendGroupsRequest;
use App\Http\Requests\Models\FriendGroup\AddFriendGroupStoresRequest;
use App\Http\Requests\Models\FriendGroup\ShowFriendGroupOrdersRequest;
use App\Http\Requests\Models\FriendGroup\ShowFriendGroupMembersRequest;
use App\Http\Requests\Models\FriendGroup\RemoveFriendGroupStoresRequest;
use App\Http\Requests\Models\FriendGroup\InviteFriendGroupMembersRequest;
use App\Http\Requests\Models\FriendGroup\RemoveFriendGroupMembersRequest;
use App\Http\Requests\Models\FriendGroup\UpdateLastSelectedFriendGroupsRequest;

class FriendGroupController extends BaseController
{
    protected FriendGroupRepository $repository;

    /**
     * FriendGroupController constructor.
     *
     * @param FriendGroupRepository $repository
     */
    public function __construct(FriendGroupRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show friend groups.
     *
     * @param ShowFriendGroupsRequest $request
     * @return JsonResponse
     */
    public function showFriendGroups(ShowFriendGroupsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->showFriendGroups($request->all()));
    }

    /**
     * Add friend group.
     *
     * @param CreateFriendGroupRequest $request
     * @return JsonResponse
     */
    public function createFriendGroup(CreateFriendGroupRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->createFriendGroup($request->all()));
    }

    /**
     * Remove friend groups.
     *
     * @param RemoveFriendGroupsRequest $request
     * @return JsonResponse
     */
    public function removeFriendGroups(RemoveFriendGroupsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->removeFriendGroups($request->input('friend_group_ids')));
    }

    /**
     * Show first created friend group.
     *
     * @return JsonResponse
     */
    public function showFirstCreatedFriendGroup(): JsonResponse
    {
        return $this->prepareOutput($this->repository->showFirstCreatedFriendGroup());
    }

    /**
     * Show last selected friend group.
     *
     * @return JsonResponse
     */
    public function showLastSelectedFriendGroup(): JsonResponse
    {
        return $this->prepareOutput($this->repository->showLastSelectedFriendGroup());
    }

    /**
     * Updated last selected friend groups.
     *
     * @param UpdateLastSelectedFriendGroupsRequest $request
     * @return JsonResponse
     */
    public function updateLastSelectedFriendGroups(UpdateLastSelectedFriendGroupsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateLastSelectedFriendGroups($request->input('friend_group_ids')));
    }

    /**
     * Check invitations to join friend groups.
     *
     * @return JsonResponse
     */
    public function checkInvitationsToJoinFriendGroups(): JsonResponse
    {
        return $this->prepareOutput($this->repository->checkInvitationsToJoinFriendGroups());
    }

    /**
     * Accept all invitations to join friend groups.
     *
     * @return JsonResponse
     */
    public function acceptAllInvitationsToJoinFriendGroups(): JsonResponse
    {
        return $this->prepareOutput($this->repository->acceptAllInvitationsToJoinFriendGroups());
    }

    /**
     * Decline all invitations to join friend groups.
     *
     * @return JsonResponse
     */
    public function declineAllInvitationsToJoinFriendGroups(): JsonResponse
    {
        return $this->prepareOutput($this->repository->declineAllInvitationsToJoinFriendGroups());
    }

    /**
     * Show friend group.
     *
     * @param string $friendGroupId
     * @return JsonResponse
     */
    public function showFriendGroup(string $friendGroupId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showFriendGroup($friendGroupId));
    }

    /**
     * Update friend group.
     *
     * @param UpdateFriendGroupRequest $request
     * @param string $friendGroupId
     * @return JsonResponse
     */
    public function updateFriendGroup(UpdateFriendGroupRequest $request, string $friendGroupId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateFriendGroup($friendGroupId, $request->all()));
    }

    /**
     * Remove friend group.
     *
     * @param string $friendGroupId
     * @return JsonResponse
     */
    public function removeFriendGroup(string $friendGroupId): JsonResponse
    {
        return $this->prepareOutput($this->repository->removeFriendGroup($friendGroupId));
    }

    /**
     * Show friend group members.
     *
     * @param ShowFriendGroupMembersRequest $request
     * @param string $friendGroupId
     * @return JsonResponse
     */
    public function showFriendGroupMembers(ShowFriendGroupMembersRequest $request, string $friendGroupId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showFriendGroupMembers($friendGroupId, $request->all()));
    }

    /**
     * Invite friend group members.
     *
     * @param InviteFriendGroupMembersRequest $request
     * @param string $friendGroupId
     * @return JsonResponse
     */
    public function inviteFriendGroupMembers(InviteFriendGroupMembersRequest $request, string $friendGroupId): JsonResponse
    {
        $role = $request->filled('role') ? UserFriendGroupRole::tryFrom($request->input('role')) : null;
        return $this->prepareOutput($this->repository->inviteFriendGroupMembers($friendGroupId, $role, $request->input('mobile_numbers')));
    }

    /**
     * Remove friend group members.
     *
     * @param RemoveFriendGroupMembersRequest $request
     * @param string $friendGroupId
     * @return JsonResponse
     */
    public function removeFriendGroupMembers(RemoveFriendGroupMembersRequest $request, string $friendGroupId): JsonResponse
    {
        return $this->prepareOutput($this->repository->removeFriendGroupMembers($friendGroupId, $request->input('mobile_numbers')));
    }

    /**
     * Leave friend group.
     *
     * @param string $friendGroupId
     * @return JsonResponse
     */
    public function leaveFriendGroup(string $friendGroupId): JsonResponse
    {
        return $this->prepareOutput($this->repository->leaveFriendGroup($friendGroupId));
    }

    /**
     * Accept invitation to join friend group.
     *
     * @param string $friendGroupId
     * @return JsonResponse
     */
    public function acceptInvitationToJoinFriendGroup(string $friendGroupId): JsonResponse
    {
        return $this->prepareOutput($this->repository->acceptInvitationToJoinFriendGroup($friendGroupId));
    }

    /**
     * Decline invitation to join friend group.
     *
     * @param string $friendGroupId
     * @return JsonResponse
     */
    public function declineInvitationToJoinFriendGroup(string $friendGroupId): JsonResponse
    {
        return $this->prepareOutput($this->repository->declineInvitationToJoinFriendGroup($friendGroupId));
    }

    /**
     * Show friend group stores.
     *
     * @param string $friendGroupId
     * @return JsonResponse
     */
    public function showFriendGroupStores(string $friendGroupId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showFriendGroupStores($friendGroupId));
    }

    /**
     * Add friend group stores.
     *
     * @param AddFriendGroupStoresRequest $request
     * @param string $friendGroupId
     * @return JsonResponse
     */
    public function addFriendGroupStores(AddFriendGroupStoresRequest $request, string $friendGroupId): JsonResponse
    {
        return $this->prepareOutput($this->repository->addFriendGroupStores($friendGroupId, $request->input('store_ids')));
    }

    /**
     * Remove friend group stores.
     *
     * @param RemoveFriendGroupStoresRequest $request
     * @param string $friendGroupId
     * @return JsonResponse
     */
    public function removeFriendGroupStores(RemoveFriendGroupStoresRequest $request, string $friendGroupId): JsonResponse
    {
        return $this->prepareOutput($this->repository->removeFriendGroupStores($friendGroupId, $request->input('store_ids')));
    }

    /**
     * Show friend group orders.
     *
     * @param ShowFriendGroupOrdersRequest $request
     * @param string $friendGroupId
     * @return JsonResponse
     */
    public function showFriendGroupOrders(ShowFriendGroupOrdersRequest $request, string $friendGroupId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showFriendGroupOrders($friendGroupId, $request->all()));
    }
}
