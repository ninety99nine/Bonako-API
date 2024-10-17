<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\FriendRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\Friend\AddFriendRequest;
use App\Http\Requests\Models\Friend\ShowFriendsRequest;
use App\Http\Requests\Models\Friend\UpdateFriendRequest;
use App\Http\Requests\Models\Friend\RemoveFriendsRequest;
use App\Http\Requests\Models\Friend\UpdateLastSelectedFriendsRequest;

class FriendController extends BaseController
{
    protected FriendRepository $repository;

    /**
     * FriendController constructor.
     *
     * @param FriendRepository $repository
     */
    public function __construct(FriendRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show friends.
     *
     * @return JsonResponse
     */
    public function showFriends(ShowFriendsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->showFriends($request->all()));
    }

    /**
     * Add friend.
     *
     * @param AddFriendRequest $request
     * @return JsonResponse
     */
    public function addFriend(AddFriendRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->addFriend($request->all()));
    }

    /**
     * Remove friends.
     *
     * @param RemoveFriendsRequest $request
     * @return JsonResponse
     */
    public function removeFriends(RemoveFriendsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->removeFriends($request->input('friend_ids')));
    }

    /**
     * Show last selected friend.
     *
     * @return JsonResponse
     */
    public function showLastSelectedFriend(): JsonResponse
    {
        return $this->prepareOutput($this->repository->showLastSelectedFriend());
    }

    /**
     * Update last selected friends.
     *
     * @param UpdateLastSelectedFriendsRequest $request
     * @return JsonResponse
     */
    public function updateLastSelectedFriends(UpdateLastSelectedFriendsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateLastSelectedFriends($request->input('friend_ids')));
    }

    /**
     * Show friend.
     *
     * @param string $friend
     * @return JsonResponse
     */
    public function showFriend(string $friend): JsonResponse
    {
        return $this->prepareOutput($this->repository->showFriend($friend));
    }

    /**
     * Update friend.
     *
     * @param UpdateFriendRequest $request
     * @param string $friendId
     * @return JsonResponse
     */
    public function updateFriend(UpdateFriendRequest $request, string $friendId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateFriend($friendId, $request->all()));
    }

    /**
     * Remove friend.
     *
     * @param string $friend
     * @return JsonResponse
     */
    public function removeFriend(string $friend): JsonResponse
    {
        return $this->prepareOutput($this->repository->removeFriend($friend));
    }
}
