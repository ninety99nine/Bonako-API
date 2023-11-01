<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\FriendGroup;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;
use App\Repositories\FriendGroupRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\FriendGroup\CreateFriendGroupRequest;
use App\Http\Requests\Models\FriendGroup\DeleteFriendGroupsRequest;
use App\Http\Requests\Models\FriendGroup\UpdateFriendGroupRequest;
use App\Http\Requests\Models\FriendGroup\ShowFriendGroupMembersRequest;
use App\Http\Requests\Models\FriendGroup\RemoveFriendFromFriendGroupRequest;
use App\Http\Requests\Models\FriendGroup\UpdateLastSelectedFriendGroupsRequest;

class FriendGroupController extends BaseController
{
    /**
     *  @var FriendGroupRepository
     */
    protected $repository;

    public function index()
    {
        return response($this->repository->get()->transform(), Response::HTTP_OK);
    }

    public function create(CreateFriendGroupRequest $request)
    {
        return response($this->repository->create($request), Response::HTTP_OK);
    }

    public function show(User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($friendGroup)->transform(), Response::HTTP_OK);
    }

    public function update(User $user, FriendGroup $friendGroup, UpdateFriendGroupRequest $request)
    {
        return response($this->repository->setModel($friendGroup)->update($request), Response::HTTP_OK);
    }

    public function delete(User $user, FriendGroup $friendGroup)
    {
        return response($this->repository->setModel($friendGroup)->delete(true), Response::HTTP_OK);
    }

    public function deleteMany(DeleteFriendGroupsRequest $request)
    {
        return response($this->repository->deleteMany($request), Response::HTTP_OK);
    }

    public function showFilters()
    {
        return response($this->repository->showFilters(), Response::HTTP_OK);
    }

    public function showLastSelectedFriendGroup()
    {
        $response = $this->repository->showLastSelectedFriendGroup();
        return response($response == null ? null : $response->transform(), Response::HTTP_OK);
    }

    public function updateLastSelectedFriendGroups(UpdateLastSelectedFriendGroupsRequest $request)
    {
        return response($this->repository->updateLastSelectedFriendGroups($request), Response::HTTP_OK);
    }

    public function showMembers(User $user, FriendGroup $friendGroup, ShowFriendGroupMembersRequest $request)
    {
        return response($this->repository->setModel($friendGroup)->showMembers($request)->transform(), Response::HTTP_OK);
    }

    public function removeMembers(User $user, FriendGroup $friendGroup, RemoveFriendFromFriendGroupRequest $request)
    {
        return response($this->repository->setModel($friendGroup)->removeMembers($request), Response::HTTP_OK);
    }

    public function showStores(User $user, FriendGroup $friendGroup, ShowFriendGroupMembersRequest $request)
    {
        return response($this->repository->setModel($friendGroup)->showStores($request)->transform(), Response::HTTP_OK);
    }

    public function showOrders(User $user, FriendGroup $friendGroup, Request $request)
    {
        return response($this->repository->setModel($friendGroup)->showOrders($request)->transform(), Response::HTTP_OK);
    }

}
