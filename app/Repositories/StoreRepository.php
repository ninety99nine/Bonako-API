<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Store;
use App\Enums\CacheName;
use App\Models\MediaFile;
use App\Traits\AuthTrait;
use App\Enums\Association;
use Illuminate\Support\Str;
use App\Enums\FollowerStatus;
use App\Helpers\CacheManager;
use App\Enums\TeamMemberRole;
use App\Enums\RequestFileName;
use App\Traits\Base\BaseTrait;
use App\Enums\TeamMemberStatus;
use App\Enums\InvitationResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResources;
use App\Http\Resources\StoreResources;
use App\Services\Filter\FilterService;
use Illuminate\Database\Eloquent\Builder;
use App\Notifications\Stores\StoreCreated;
use App\Http\Resources\MediaFileResources;
use App\Models\Pivots\UserStoreAssociation;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\Eloquent\Collection;
use App\Services\PhoneNumber\PhoneNumberService;
use App\Notifications\Users\RemoveStoreTeamMember;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Notifications\Users\InvitationToFollowStoreCreated;
use App\Notifications\Users\InvitationToFollowStoreAccepted;
use App\Notifications\Users\InvitationToFollowStoreDeclined;
use App\Notifications\Users\InvitationToJoinStoreTeamCreated;
use App\Notifications\Users\InvitationToJoinStoreTeamAccepted;
use App\Notifications\Users\InvitationToJoinStoreTeamDeclined;

class StoreRepository extends BaseRepository
{
    use AuthTrait, BaseTrait;

    /**
     * Show stores.
     *
     * @param array $data
     * @return StoreResources|array
     */
    public function showStores(array $data = []): StoreResources|array
    {
        if($this->getQuery() == null) {

            $userId = isset($data['user_id']) ? $data['user_id'] : null;
            $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;

            if($association == Association::SUPER_ADMIN) {
                if(!$this->isAuthourized()) return ['message' => 'You do not have permission to show stores'];
                $this->setQuery(Store::query()->latest());
            }else{

                $user = in_array($userId, [request()->current_user->id, null]) ? request()->current_user : User::find($userId);

                if($user) {
                    $isAuthourized = $this->isAuthourized() || $user->id == request()->auth_user->id;
                    if(!$isAuthourized) return ['message' => 'You do not have permission to show reviews'];
                }else{
                    return ['message' => 'This user does not exist'];
                }

                if($association == Association::FOLLOWER) {
                    $this->setQuery($user->storesAsFollower()->orderByPivot('last_seen_at', 'DESC'));
                }elseif($association == Association::CUSTOMER) {
                    $this->setQuery($user->storesAsCustomer()->orderByPivot('last_seen_at', 'DESC'));
                }elseif($association == Association::TEAM_MEMBER) {
                    $this->setQuery($user->storesAsTeamMember()->orderByPivot('last_seen_at', 'DESC'));
                }elseif($association == Association::RECENT_VISITOR) {
                    $this->setQuery($user->storesAsRecentVisitor()->orderByPivot('last_seen_at', 'DESC'));
                }else{
                    $this->setQuery($user->stores()->orderByPivot('last_seen_at', 'DESC'));
                }
            }

        }

        return $this->applyFiltersOnQuery()->getOrCountResources();
    }

    /**
     * Create store.
     *
     * @param array $data
     * @return Store|array
     */
    public function createStore(array $data): Store|array
    {
        $store = Store::create($data);
        $this->addStoreCreator($store, request()->current_user);
        $this->getMediaFileRepository()->createMediaFile(RequestFileName::STORE_LOGO, $store);
        $this->getMediaFileRepository()->createMediaFile(RequestFileName::STORE_COVER_PHOTO, $store);
        Notification::send(request()->current_user, new StoreCreated($store, request()->current_user));
        return $this->showCreatedResource($store);
    }

    /**
     * Search store by alias.
     *
     * @param string $alias
     * @return Store|array|null
     */
    public function searchStoreByAlias(string $alias): Store|array|null
    {
        $query = Store::searchAlias($alias);
        $this->setQuery($query)->applyEagerLoadingOnQuery();
        $store = $this->query->first();
        return $this->showResourceExistence($store);
    }

    /**
     * Search store by USSD mobile number.
     *
     * @param string $ussdMobileNumber
     * @return Store|array|null
     */
    public function searchStoreByUssdMobileNumber(string $ussdMobileNumber): Store|array|null
    {
        if(!Str::startsWith($ussdMobileNumber, '+')) $ussdMobileNumber = '+'. (string) $ussdMobileNumber;
        $query = Store::searchUssdMobileNumber($ussdMobileNumber);
        $this->setQuery($query)->applyEagerLoadingOnQuery();
        $store = $this->query->first();
        return $this->showResourceExistence($store);
    }

    /**
     * Check invitations to follow stores.
     *
     * @return array
     */
    public function checkInvitationsToFollowStores(): array
    {
        $invitations = DB::table('user_store_association')
                        ->where('user_id', request()->current_user->id)
                        ->where('follower_status', FollowerStatus::INVITED)
                        ->get();

        $totalInvitations = count($invitations);
        $hasInvitations = $totalInvitations > 0;

        return [
            'has_invitations' => $hasInvitations,
            'total_invitations' => $totalInvitations,
        ];
    }

    /**
     * Accept all invitations to follow stores.
     *
     * @return array
     */
    public function acceptAllInvitationsToFollowStores(): array
    {
        $userId = request()->current_user->id;
        $invitedStatus = FollowerStatus::INVITED;
        $followingStatus = FollowerStatus::FOLLOWING;

        // Get the stores that the user has been invited to follow
        $stores = Store::with(['teamMembers' => fn($query) => $query->joinedTeam()])
            ->whereHas('followers', fn($query) => $query->where([
                'follower_status' => $invitedStatus,
                'user_id' => $userId
            ]))->get();

        // Accept the invitations
        DB::table('user_store_association')
            ->where('user_id', $userId)
            ->where('follower_status', $invitedStatus)
            ->update(['follower_status' => $followingStatus]);

        if($stores->isNotEmpty()) {
            $this->clearCacheOnAssociationAsFollower($userId);
            $this->notifyStoreTeamMembersOnUserResponseToFollowInvitation(InvitationResponse::ACCEPTED, $stores);
        }

        return ['message' => 'Invitations accepted successfully'];
    }

    /**
     * Decline all invitations to follow stores.
     *
     * @return array
     */
    public function declineAllInvitationsToFollowStores(): array
    {
        $userId = request()->current_user->id;
        $invitedStatus = FollowerStatus::INVITED;
        $declinedStatus = FollowerStatus::DECLINED;

        // Get the stores that the user has been invited to follow
        $stores = Store::with(['teamMembers' => fn($query) => $query->joinedGroup()])
            ->whereHas('followers', fn($query) => $query->where([
                'follower_status' => $invitedStatus,
                'user_id' => $userId
            ]))->get();

        // Decline the invitations
        DB::table('user_store_association')
            ->where('user_id', $userId)
            ->where('follower_status', $invitedStatus)
            ->update(['follower_status' => $declinedStatus]);

        if($stores->isNotEmpty()) {
            $this->clearCacheOnAssociationAsFollower($userId);
            $this->notifyStoreTeamMembersOnUserResponseToFollowInvitation(InvitationResponse::DECLINED, $stores);
        }

        return ['message' => 'Invitations declined successfully'];
    }

    /**
     * Check invitations to join stores.
     *
     * @return array
     */
    public function checkInvitationsToJoinStores(): array
    {
        $invitations = DB::table('user_store_association')
                        ->where('user_id', request()->current_user->id)
                        ->where('team_member_status', TeamMemberStatus::INVITED)
                        ->get();

        $totalInvitations = count($invitations);
        $hasInvitations = $totalInvitations > 0;

        return [
            'has_invitations' => $hasInvitations,
            'total_invitations' => $totalInvitations,
        ];
    }

    /**
     * Accept all invitations to join stores.
     *
     * @return array
     */
    public function acceptAllInvitationsToJoinStores(): array
    {
        $userId = request()->current_user->id;
        $joinedStatus = TeamMemberStatus::JOINED;
        $invitedStatus = TeamMemberStatus::INVITED;

        // Get the stores that the user has been invited to join
        $stores = Store::with(['teamMembers' => fn($query) => $query->joinedTeam()])
            ->whereHas('teamMembers', fn($query) => $query->where([
                'team_member_status' => $invitedStatus,
                'user_id' => $userId
            ]))->get();

        // Accept the invitations
        DB::table('user_store_association')
            ->where('user_id', $userId)
            ->where('team_member_status', $invitedStatus)
            ->update(['team_member_status' => $joinedStatus]);

        if($stores->isNotEmpty()) {
            $this->clearCacheOnAssociationAsTeamMember($userId);
            $this->notifyStoreTeamMembersOnUserResponseToJoinInvitation(InvitationResponse::ACCEPTED, $stores);
        }

        return ['message' => 'Invitations accepted successfully'];
    }

    /**
     * Decline all invitations to join stores.
     *
     * @return array
     */
    public function declineAllInvitationsToJoinStores(): array
    {
        $userId = request()->current_user->id;
        $invitedStatus = TeamMemberStatus::INVITED;
        $declinedStatus = TeamMemberStatus::DECLINED;

        // Get the stores that the user has been invited to join
        $stores = Store::with(['teamMembers' => fn($query) => $query->joinedGroup()])
            ->whereHas('teamMembers', fn($query) => $query->where([
                'team_member_status' => $invitedStatus,
                'user_id' => $userId
            ]))->get();

        // Decline the invitations
        DB::table('user_store_association')
            ->where('user_id', $userId)
            ->where('team_member_status', $invitedStatus)
            ->update(['team_member_status' => $declinedStatus]);

        if($stores->isNotEmpty()) {
            $this->clearCacheOnAssociationAsTeamMember($userId);
            $this->notifyStoreTeamMembersOnUserResponseToJoinInvitation(InvitationResponse::DECLINED, $stores);
        }

        return ['message' => 'Invitations declined successfully'];
    }

    /**
     * Show store.
     *
     * @param Store|string|null $storeId
     * @return Store|array|null
     */
    public function showStore(Store|string $storeId = null): Store|array|null
    {
        if(($store = $storeId) instanceof Store) {
            $store = $this->applyEagerLoadingOnModel($store);
        }else {
            $query = $this->getQuery() ?? Store::query();
            if($storeId) $query = $query->where('stores.id', $storeId);
            $this->setQuery($query)->applyEagerLoadingOnQuery();
            $store = $this->query->first();
        }

        return $this->showResourceExistence($store);
    }

    /**
     * Update store.
     *
     * @param string $storeId
     * @param array $data
     * @return Store|array
     */
    public function updateStore(string $storeId, array $data): Store|array
    {
       $store = Store::find($storeId);

        if($store) {

            $isAuthourized = $this->isAuthourized() || $this->checkIfAssociatedAsStoreCreatorOrAdmin($store);

            if($isAuthourized) {

                $store->update($data);
                return $this->showUpdatedResource($store);

            }else{
                return ['updated' => false, 'message' => 'You do not have permission to update this store'];
            }

        }else{
            return ['updated' => false, 'message' => 'This store does not exist'];
        }
    }

    /**
     * Delete store.
     *
     * @param string $storeId
     * @return array
     */
    public function deleteStore(string $storeId): array
    {
       $store = Store::find($storeId);

        if($store) {

            $isAuthourized = $this->isAuthourized() || $this->checkIfAssociatedAsStoreCreator($store);

            if($isAuthourized) {

                $deleted = $store->delete();

                if ($deleted) {
                    return ['deleted' => true, 'message' => 'Store deleted'];
                }else{
                    return ['deleted' => false, 'message' => 'Store delete unsuccessful'];
                }

            }else{
                return ['deleted' => false, 'message' => 'You do not have permission to delete this store'];
            }

        }else{
            return ['deleted' => false, 'message' => 'This store does not exist'];
        }
    }

    /**
     * Show store logo.
     *
     * @param string $storeId
     * @return array
     */
    public function showStoreLogo(string $storeId): array
    {
        $store = Store::find($storeId);

        if($store) {
            return $this->getMediaFileRepository()->setQuery($store->logo())->showMediaFile();
        }else{
            return ['message' => 'This store does not exist'];
        }
    }

    /**
     * Upload store logo.
     *
     * @param string $storeId
     * @return MediaFile|array
     */
    public function uploadStoreLogo(string $storeId): MediaFile|array
    {
        $store = Store::with(['logo'])->find($storeId);

        if($store) {

            $isAuthourized = $this->isAuthourized() || $this->checkIfAssociatedAsStoreCreatorOrAdmin($store);

            if($isAuthourized) {

                if($store->logo) {
                    $result = $this->getMediaFileRepository()->updateMediaFile($store->logo);
                }else{
                    $result = $this->getMediaFileRepository()->createMediaFile(RequestFileName::STORE_LOGO, $store);
                }

                $uploaded = (isset($result['created']) && $result['created'] == true) || (isset($result['updated']) && $result['updated'] == true);

                if($uploaded) {

                    $mediaFile = isset($result['media_file']) ? $result['media_file'] : $result['media_files'][0];
                    return $this->showSavedResource($mediaFile, 'uploaded', 'Store logo uploaded');

                }else{
                    return ['uploaded' => false, 'message' => $result['message']];
                }

            }else{
                return ['uploaded' => false, 'message' => 'You do not have permission to update this store logo'];
            }

        }else{
            return ['uploaded' => false, 'message' => 'This store does not exist'];
        }
    }

    /**
     * Show store cover photo.
     *
     * @param string $storeId
     * @return array
     */
    public function showStoreCoverPhoto(string $storeId): array
    {
        $store = Store::find($storeId);

        if($store) {
            return $this->getMediaFileRepository()->setQuery($store->coverPhoto())->showMediaFile();
        }else{
            return ['message' => 'This store does not exist'];
        }
    }

    /**
     * Upload store cover photo.
     *
     * @param string $storeId
     * @return MediaFile|array
     */
    public function uploadStoreCoverPhoto(string $storeId): MediaFile|array
    {
        $store = Store::with(['coverPhoto'])->find($storeId);

        if($store) {

            $isAuthourized = $this->isAuthourized() || $this->checkIfAssociatedAsStoreCreatorOrAdmin($store);

            if($isAuthourized) {

                if($store->coverPhoto) {
                    $result = $this->getMediaFileRepository()->updateMediaFile($store->coverPhoto);
                }else{
                    $result = $this->getMediaFileRepository()->createMediaFile(RequestFileName::STORE_COVER_PHOTO, $store);
                }

                $uploaded = (isset($result['created']) && $result['created'] == true) || (isset($result['updated']) && $result['updated'] == true);

                if($uploaded) {

                    $mediaFile = isset($result['media_file']) ? $result['media_file'] : $result['media_files'][0];
                    return $this->showSavedResource($mediaFile, 'uploaded', 'Store cover photo uploaded');

                }else{
                    return ['uploaded' => false, 'message' => $result['message']];
                }

            }else{
                return ['uploaded' => false, 'message' => 'You do not have permission to update this store cover photo'];
            }

        }else{
            return ['uploaded' => false, 'message' => 'This store does not exist'];
        }
    }

    /**
     * Show store adverts.
     *
     * @param string $storeId
     * @return MediaFileResources|array
     */
    public function showStoreAdverts(string $storeId): MediaFileResources|array
    {
       $store = Store::find($storeId);

        if($store) {
            return $this->getMediaFileRepository()->setQuery($store->adverts())->showMediaFiles();
        }else{
            return ['message' => 'This store does not exist'];
        }
    }

    /**
     * Upload store advert.
     *
     * @param string $storeId
     * @return array
     */
    public function uploadStoreAdvert(string $storeId): array
    {
       $store = Store::find($storeId);

        if($store) {

            $isAuthourized = $this->isAuthourized() || $this->checkIfAssociatedAsStoreCreatorOrAdmin($store);

            if($isAuthourized) {
                return $this->getMediaFileRepository()->createMediaFile(RequestFileName::STORE_ADVERT, $store);
            }else{
                return ['created' => false, 'message' => 'You do not have permission to upload store advert'];
            }

        }else{
            return ['created' => false, 'message' => 'This store does not exist'];
        }
    }

    /**
     * Show store quick start guide.
     *
     * @param string $storeId
     * @return array
     */
    public function showStoreQuickStartGuide(string $storeId): array
    {
       $store = Store::find($storeId);

        if($store) {

            $userStoreAssociation = $this->getUserStoreAssociation($store);
            $isAuthourized = $this->isAuthourized() || ($userStoreAssociation && $this->checkIfAssociatedAsStoreCreatorOrAdmin($store, $userStoreAssociation));

            if($isAuthourized) {

                $totalOrders = $store->orders()->exists();
                $lastSeenOnUssdAt = $userStoreAssociation->last_seen_on_ussd_at;
                $lastSubscriptionEndAt = $userStoreAssociation->last_subscription_end_at;
                $totalProducts = $store->products()->isNotVariation()->visible()->count();

                $milestones = [
                    [
                        'title' => 'Store created',
                        'type' => 'created store',
                        'status' => true,
                        'created_at' => $store->created_at,
                    ],
                    [
                        'title' => $totalProducts == 0 ? 'Add products' : 'Added ' . $totalProducts . ' ' . ($totalProducts == 1 ? 'product' : 'products'),
                        'type' => 'added products',
                        'status' => $totalProducts > 0,
                        'total_products' => $totalProducts,
                    ],
                    [
                        'title' => (is_null($lastSeenOnUssdAt) ? 'Dial' : 'Dialed'). ' your store on ' . request()->auth_user->mobile_number_shortcode,
                        'type' => 'dialed store',
                        'status' => !is_null($lastSeenOnUssdAt),
                        'last_seen_on_ussd_at' => $lastSeenOnUssdAt,
                        'mobile_number_shortcode' => request()->auth_user->mobile_number_shortcode,
                    ],
                    [
                        'title' => is_null($lastSubscriptionEndAt) ? 'Open for business by Subscribing' : 'Subscribed until ' . $lastSubscriptionEndAt->format('d M Y @ H:i'),
                        'type' => 'subscribed',
                        'status' => !is_null($lastSubscriptionEndAt) && $lastSubscriptionEndAt->isFuture(),
                        'subscription_end_at' => $lastSubscriptionEndAt,
                    ],
                    [
                        'title' => $totalOrders == 0 ? 'Receive your first order' : 'Received ' . $totalOrders . ' ' . ($totalOrders == 1 ? 'order' : 'orders'),
                        'type' => 'received orders',
                        'status' => $totalOrders > 0,
                        'total_orders' => $totalOrders,
                    ],
                ];

                $completedMilestones = array_filter($milestones, fn($milestone) => $milestone['status']);
                $completedCount = count($completedMilestones);
                $totalMilestones = count($milestones);

                return [
                    'title' => "Here's a guide to get you selling in minutes.",
                    'completed_milestones' => $completedCount,
                    'total_milestones' => $totalMilestones,
                    'milestones' => $milestones
                ];

            }else{
                return ['message' => 'You do not have permission to show store quick start guide'];
            }

        }else{
            return ['message' => 'This store does not exist'];
        }
    }

    /**
     * Show store followers.
     *
     * @param string $storeId
     * @return UserResources|array
     */
    public function showStoreFollowers(string $storeId): UserResources|array
    {
       $store = Store::find($storeId);

        if($store) {
            return $this->getUserRepository()->setQuery($store->followers())->showUsers();
        }else{
            return ['message' => 'This store does not exist'];
        }
    }

    /**
     * Invite store followers.
     *
     * @param Store|string $storeId
     * @param array $mobileNumbers
     * @return array
     */
    public function inviteStoreFollowers(Store|string $storeId, array $mobileNumbers): array
    {
       $store = Store::find($storeId);

        if($store) {

            $isAuthourized = $this->isAuthourized() || $this->checkIfAssociatedAsStoreCreatorOrAdmin($store);

            if($isAuthourized) {

                $assignedUsers = $this->getAssignedFollowers($store, $mobileNumbers);
                $notAssignedUsers = $this->getNotAssignedFollowers($store, $mobileNumbers);
                $mobileNumbersThatDontMatchAnyUserButInvited = $this->getNonExistingFollowersButInvited($store, $mobileNumbers);
                $mobileNumbersThatDontMatchAnyUser = $this->getNonMatchingMobileNumbers($mobileNumbers, array_merge(
                    $mobileNumbersThatDontMatchAnyUserButInvited,
                    collect($assignedUsers)->map(fn(User $assignedUser) => $assignedUser->mobile_number->formatE164())->toArray(),
                    collect($notAssignedUsers)->map(fn(User $notAssignedUser) => $notAssignedUser->mobile_number->formatE164())->toArray()
                ));

                if($notAssignedUsers->isNotEmpty()) {
                    $this->addFollowers($store, $notAssignedUsers, FollowerStatus::INVITED);
                }

                if(!empty($mobileNumbersThatDontMatchAnyUser)) {
                    $this->addFollowersByMobileNumbers($store, $mobileNumbersThatDontMatchAnyUser);
                }

                $message = $this->prepareInvitationMessage($mobileNumbers, $assignedUsers);
                $invitations = $this->prepareInvitationSummary($notAssignedUsers, $assignedUsers, $mobileNumbersThatDontMatchAnyUser, $mobileNumbersThatDontMatchAnyUserButInvited);

                return [
                    'invited' => true,
                    'message' => $message,
                    'invitations' => $invitations
                ];

            }else{
                return ['invited' => false, 'message' => 'You do not have permission to invite store followers'];
            }

        }else{
            return ['invited' => false, 'message' => 'This store does not exist'];
        }
    }

    /**
     * Show store following.
     *
     * @param string $storeId
     * @return array
     */
    public function showStoreFollowing(string $storeId): array
    {
       $store = Store::find($storeId);

        if($store) {

            $userStoreAssociation = $this->getUserStoreAssociation($store);
            return ['following' => $userStoreAssociation && $userStoreAssociation->is_follower];

        }else{
            return ['message' => 'This store does not exist'];
        }
    }

    /**
     * Update store following.
     *
     * @param string $storeId
     * @return array
     */
    public function updateStoreFollowing(string $storeId): array
    {
       $store = Store::find($storeId);

        if($store) {

            $userStoreAssociation = $this->getUserStoreAssociation($store);

            if($userStoreAssociation) {

                DB::table('user_store_association')->where([
                    'user_id' => request()->current_user->id,
                    'store_id' => $store->id
                ])->update(['follower_status' => $userStoreAssociation->is_follower ? FollowerStatus::UNFOLLOWED : FollowerStatus::FOLLOWING,]);

                $following = !$userStoreAssociation->is_follower;

            }else{

                DB::table('user_store_association')->insert([
                    'follower_status' => FollowerStatus::FOLLOWING,
                    'user_id' => request()->current_user->id,
                    'store_id' => $store->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'id' => Str::uuid()
                ]);

                $following = true;

            }

            return [
                'message' => $following ? 'You are following' : 'You are not following',
                'follower_status' => $following ? 'Following' : 'Unfollowed',
                'following' => $following
            ];

        }else{
            return ['message' => 'This store does not exist'];
        }
    }

    /**
     * Accept invitation to follow store.
     *
     * @param string $storeId
     * @return array
     */
    public function acceptInvitationToFollowStore(string $storeId): array
    {
       $store = Store::find($storeId);

        if($store) {

            $storeUserAssociation = $this->getUserStoreAssociation($store);

            if($storeUserAssociation) {

                if($storeUserAssociation->is_follower_who_is_invited) {

                    $this->updateInvitationStatusToFollowStore($store, FollowerStatus::FOLLOWING);
                    $this->notifyStoreTeamMembersOnUserResponseToFollowInvitation(InvitationResponse::ACCEPTED, $store);
                    return ['accepted' => true, 'message' => 'Invitation accepted successfully'];

                }elseif($storeUserAssociation->is_follower) {
                    return ['accepted' => true, 'message' => 'Invitation already accepted'];
                }elseif($storeUserAssociation->is_unfollower) {
                    return ['accepted' => false, 'message' => 'You have already unfollowed'];
                }elseif($storeUserAssociation->is_follower_who_has_declined) {
                    return ['accepted' => false, 'message' => 'Invitation has already been declined and cannot be accepted. Request the store creator or admin to resend the invitation again.'];
                }

            }else{
                return ['accepted' => false, 'message' => 'You have not been invited to follow this store'];
            }

        }else{
            return ['accepted' => false, 'message' => 'This store does not exist'];
        }
    }

    /**
     * Decline invitation to follow store.
     *
     * @param string $storeId
     * @return array
     */
    public function declineInvitationToFollowStore(string $storeId): array
    {
       $store = Store::find($storeId);

        if($store) {

            $storeUserAssociation = $this->getUserStoreAssociation($store);

            if($storeUserAssociation) {

                if($storeUserAssociation->is_follower_who_is_invited) {

                    $this->updateInvitationStatusToFollowStore($store, FollowerStatus::DECLINED);
                    $this->notifyStoreTeamMembersOnUserResponseToFollowInvitation(InvitationResponse::DECLINED, $store);
                    return ['declined' => true, 'message' => 'Invitation declined successfully'];

                }elseif($storeUserAssociation->is_follower) {
                    return ['declined' => false, 'message' => 'Invitation has already been accepted and cannot be declined.'];
                }elseif($storeUserAssociation->is_unfollower) {
                    return ['declined' => false, 'message' => 'You have already unfollowed'];
                }elseif($storeUserAssociation->is_follower_who_has_declined) {
                    return ['declined' => true, 'message' => 'Invitation already declined'];
                }

            }else{
                return ['declined' => false, 'message' => 'You have not been invited to follow this store'];
            }

        }else{
            return ['declined' => false, 'message' => 'This store does not exist'];
        }
    }

    /**
     * Show team member permission options.
     *
     * @return array
     */
    public function showTeamMemberPermissionOptions(): array
    {
        return $this->extractPermissions(['*']);
    }

    /**
     * Show my store permissions.
     *
     * @return array
     */
    public function showMyStorePermissions(string $storeId): array
    {
       $store = Store::find($storeId);

        if($store) {

            $storeUserAssociation = $this->getUserStoreAssociation($store);

            if($storeUserAssociation && $storeUserAssociation->is_team_member_who_has_joined) {

                return $storeUserAssociation->team_member_permissions;

            }else{
                return ['message' => 'You are not a team member on this store'];
            }

        }else{
            return ['message' => 'This store does not exist'];
        }
    }

    /**
     * Show store team members.
     *
     * @param string $storeId
     * @return UserResources|array
     */
    public function showStoreTeamMembers(string $storeId): UserResources|array
    {
       $store = Store::find($storeId);

        if($store) {
            return $this->getUserRepository()->setQuery($store->teamMembers())->showUsers();
        }else{
            return ['message' => 'This store does not exist'];
        }
    }

    /**
     * Invite store team members.
     *
     * @param string $storeId
     * @param array $mobileNumbers
     * @param array $permissions
     * @return array
     */
    public function inviteStoreTeamMembers(string $storeId, array $mobileNumbers, array $permissions): array
    {
       $store = Store::find($storeId);

        if($store) {

            $isAuthourized = $this->isAuthourized() || $this->checkIfAssociatedAsStoreCreatorOrAdmin($store);

            if($isAuthourized) {

                $assignedUsers = $this->getAssignedTeamMembers($store, $mobileNumbers);
                $notAssignedUsers = $this->getNotAssignedTeamMembers($store, $mobileNumbers);
                $mobileNumbersThatDontMatchAnyUserButInvited = $this->getNonExistingTeamMembersButInvited($store, $mobileNumbers);
                $mobileNumbersThatDontMatchAnyUser = $this->getNonMatchingMobileNumbers($mobileNumbers, array_merge(
                    $mobileNumbersThatDontMatchAnyUserButInvited,
                    collect($assignedUsers)->map(fn(User $assignedUser) => $assignedUser->mobile_number->formatE164())->toArray(),
                    collect($notAssignedUsers)->map(fn(User $notAssignedUser) => $notAssignedUser->mobile_number->formatE164())->toArray()
                ));

                if($notAssignedUsers->isNotEmpty()) {
                    $this->addTeamMembers($store, $notAssignedUsers, TeamMemberStatus::INVITED, $permissions);
                }

                if(!empty($mobileNumbersThatDontMatchAnyUser)) {
                    $this->addTeamMembersByMobileNumbers($store, $mobileNumbersThatDontMatchAnyUser, $permissions);
                }

                $message = $this->prepareInvitationMessage($mobileNumbers, $assignedUsers);
                $invitations = $this->prepareInvitationSummary($notAssignedUsers, $assignedUsers, $mobileNumbersThatDontMatchAnyUser, $mobileNumbersThatDontMatchAnyUserButInvited);

                return [
                    'invited' => true,
                    'message' => $message,
                    'invitations' => $invitations
                ];

            }else{
                return ['invited' => false, 'message' => 'You do not have permission to invite store team members'];
            }

        }else{
            return ['invited' => false, 'message' => 'This store does not exist'];
        }
    }

    /**
     * Remove store team members.
     *
     * @param string $storeId
     * @param array $mobileNumbers
     * @return array
     */
    public function removeStoreTeamMembers(string $storeId, array $mobileNumbers): array
    {
       $store = Store::find($storeId);

        if($store) {

            $isAuthourized = $this->isAuthourized() || $this->checkIfAssociatedAsStoreCreatorOrAdmin($store);

            if($isAuthourized) {

                $assignedUsers = $store->teamMembers()
                    ->whereIn('user_store_association.mobile_number', $mobileNumbers)
                    ->orWhereIn('users.mobile_number', $mobileNumbers)
                    ->get();

                $assignedUsers = $assignedUsers->reject(function ($user) {
                    return ($user->id === request()->current_user->id) || $user->user_store_association->is_team_member_as_creator;
                });

                if($assignedUsers->isEmpty()) {
                    return ['removed' => false, 'message' => 'No store team members removed'];
                }

                collect($assignedUsers)->each(function(User $assignedUser) use ($store) {
                    collect(Store::PERMISSIONS)->each(function($permission) use ($assignedUser, $store) {
                        $assignedUser->removeHasStorePermissionFromCache($store->id, $permission['grant']);
                    });
                });

                $userStoreAssociationIds = $assignedUsers->pluck('user_store_association.id')->toArray();

                DB::table('user_store_association')->whereIn('id', $userStoreAssociationIds)->update([
                    'invited_to_join_team_by_user_id' => null,
                    'team_member_permissions' => null,
                    'team_member_status' => null,
                    'team_member_role' => null
                ]);

                $teamMembers = $store->teamMembers()->joinedTeam()->get();

                foreach ($assignedUsers as $removedUser) {
                    Notification::send($teamMembers, new RemoveStoreTeamMember($store, $removedUser, request()->current_user));
                }

                return ['removed' => true, 'message' => count($userStoreAssociationIds) . ' store ' . (count($userStoreAssociationIds) === 1 ? 'team member' : 'team members') . ' removed'];

            }else{
                return ['removed' => false, 'message' => 'You do not have permission to remove store team members'];
            }

        }else{
            return ['removed' => false, 'message' => 'This store does not exist'];
        }
    }

    /**
     * Show store team member.
     *
     * @param string $storeId
     * @param string $teamMemberId
     * @return UserResources|array
     */
    public function showStoreTeamMember(string $storeId, string $teamMemberId): UserResources|array
    {
       $store = Store::find($storeId);

        if($store) {
            return $this->getUserRepository()->setQuery($store->teamMembers())->showUser($teamMemberId);
        }else{
            return ['message' => 'This store does not exist'];
        }
    }

    /**
     * Show store team member permissions.
     *
     * @param string $storeId
     * @param string $teamMemberId
     * @return array
     */
    public function showStoreTeamMemberPermissions(string $storeId, string $teamMemberId): array
    {
       $store = Store::find($storeId);

        if($store) {

            $teamMember = $this->getUserRepository()->setQuery($store->teamMembers())->shouldReturnModel()->showUser($teamMemberId);

            if($teamMember) {

                $storeUserAssociation = $this->getUserStoreAssociation($store, $teamMemberId);

                if($storeUserAssociation && $storeUserAssociation->is_team_member_who_has_joined) {

                    return $storeUserAssociation->team_member_permissions;

                }else{
                    return ['message' => 'This user is not a team member on this store'];
                }

            }else{
                return ['message' => 'This team member does not exist'];
            }

        }else{
            return ['message' => 'This store does not exist'];
        }
    }

    /**
     * Update store team member permissions.
     *
     * @param string $storeId
     * @param string $teamMemberId
     * @param array<string> $teamMemberPermissions
     * @return array
     */
    public function updateStoreTeamMemberPermissions(string $storeId, string $teamMemberId, array $teamMemberPermissions): array
    {
       $store = Store::find($storeId);

        if($store) {

            $isAuthourized = $this->isAuthourized() || $this->checkIfAssociatedAsStoreCreatorOrAdmin($store);

            if($isAuthourized) {

                $teamMember = $this->getUserRepository()->setQuery($store->teamMembers())->shouldReturnModel()->showUser($teamMemberId);

                if($teamMember) {

                    if($teamMember-> id == $this->getAuthUser()->id) return ['updated' => false, 'message' => 'You cannot change your own store permissions'];

                    $storeUserAssociation = $this->getUserStoreAssociation($store, $teamMemberId);

                    if($storeUserAssociation && $storeUserAssociation->is_team_member_who_has_joined) {

                        if($storeUserAssociation->is_team_member_as_creator) return ['updated' => false, 'message' => 'You cannot change permissions of the store creator'];

                        $teamMemberRole = $this->determineRoleBasedOnPermissions($teamMemberPermissions);

                        DB::table('user_store_association')->where([
                            'user_id' => $teamMemberId,
                            'store_id' => $store->id,
                        ])->update([
                            'team_member_permissions' => json_encode($teamMemberPermissions),
                            'team_member_role' => $teamMemberRole->value,
                            'updated_at' => now()
                        ]);

                        return ['updated' => true, 'message' => 'Team member permissions updated'];

                    }else{
                        return ['updated' => false, 'message' => 'This user is not a team member on this store'];
                    }

                }else{
                    return ['updated' => false, 'message' => 'This team member does not exist'];
                }

            }else{
                return ['updated' => false, 'message' => 'You do not have permission to update store team member permissions'];
            }

        }else{
            return ['updated' => false, 'message' => 'This store does not exist'];
        }
    }

    /**
     * Accept invitation to join store team.
     *
     * @param string $storeId
     * @return array
     */
    public function acceptInvitationToJoinStoreTeam(string $storeId): array
    {
       $store = Store::find($storeId);

        if($store) {

            $storeUserAssociation = $this->getUserStoreAssociation($store);

            if($storeUserAssociation) {

                if($storeUserAssociation->is_team_member_who_is_invited) {

                    $this->updateInvitationStatusToJoinStoreTeam($store, TeamMemberStatus::JOINED);
                    $this->notifyStoreTeamMembersOnUserResponseToJoinInvitation(InvitationResponse::ACCEPTED, $store);
                    return ['accepted' => true, 'message' => 'Invitation accepted successfully'];

                }elseif($storeUserAssociation->is_team_member_who_has_joined) {
                    return ['accepted' => true, 'message' => 'Invitation already accepted'];
                }elseif($storeUserAssociation->is_team_member_who_has_left) {
                    return ['accepted' => false, 'message' => 'You have already left the store team'];
                }elseif($storeUserAssociation->is_team_member_who_has_declined) {
                    return ['accepted' => false, 'message' => 'Invitation has already been declined and cannot be accepted. Request the store creator or admin to resend the invitation again.'];
                }

            }else{
                return ['accepted' => false, 'message' => 'You have not been invited to join this store'];
            }

        }else{
            return ['accepted' => false, 'message' => 'This store does not exist'];
        }
    }

    /**
     * Decline invitation to join store team.
     *
     * @param string $storeId
     * @return array
     */
    public function declineInvitationToJoinStoreTeam(string $storeId): array
    {
       $store = Store::find($storeId);

        if($store) {

            $storeUserAssociation = $this->getUserStoreAssociation($store);

            if($storeUserAssociation) {

                if($storeUserAssociation->is_team_member_who_is_invited) {

                    $this->updateInvitationStatusToJoinStoreTeam($store, TeamMemberStatus::DECLINED);
                    $this->notifyStoreTeamMembersOnUserResponseToJoinInvitation(InvitationResponse::DECLINED, $store);
                    return ['declined' => true, 'message' => 'Invitation declined successfully'];

                }elseif($storeUserAssociation->is_team_member_who_has_joined) {
                    return ['declined' => false, 'message' => 'Invitation has already been accepted and cannot be declined.'];
                }elseif($storeUserAssociation->is_team_member_who_has_left) {
                    return ['declined' => false, 'message' => 'You have already left the store team'];
                }elseif($storeUserAssociation->is_team_member_who_has_declined) {
                    return ['declined' => true, 'message' => 'Invitation already declined'];
                }

            }else{
                return ['declined' => false, 'message' => 'You have not been invited to join this store'];
            }

        }else{
            return ['declined' => false, 'message' => 'This store does not exist'];
        }
    }

    /**
     * Show store subscriptions.
     *
     * @param string $storeId
     * @return SubscriptionRepository|array
     */
    public function showStoreSubscriptions(string $storeId): SubscriptionRepository|array
    {
       $store = Store::find($storeId);

        if($store) {
            return $this->getSubscriptionRepository()->setQuery($store->subscriptions())->showSubscriptions();
        }else{
            return ['message' => 'This store does not exist'];
        }
    }

    /**
     * Update store quota.
     *
     * @param Store|string $storeId
     * @param array $data
     * @return Store|array
     */
    public function updateStoreQuota(Store|string $storeId, array $data): Store|array
    {
       $store = $storeId instanceof Store ? Store::find($storeId) : $storeId;

        if($store) {

            if(!$this->isAuthourized() && !$this->isAuthourized()) return ['updated' => false, 'message' => 'You do not have permission to update this store quota'];

            $store->storeQuota()->update($data);
            return $this->showUpdatedResource($store, 'Store quota updated');

        }else{
            return ['updated' => false, 'message' => 'This store does not exist'];
        }
    }

    /**
     * Show store transactions.
     *
     * @param string $storeId
     * @return TransactionRepository|array
     */
    public function showStoreTransactions(string $storeId): TransactionRepository|array
    {
       $store = Store::find($storeId);

        if($store) {
            return $this->getTransactionRepository()->setQuery($store->transactions())->showTransactions();
        }else{
            return ['message' => 'This store does not exist'];
        }
    }

    /***********************************************
     *           MISCELLANEOUS METHODS           *
     **********************************************/

    /**
     * Query store by ID.
     *
     * @param string $storeId
     * @param array $relationships
     * @return Builder|Relation
     */
    public function queryStoreById(string $storeId, array $relationships = []): Builder|Relation
    {
        return $this->query->where('stores.id', $storeId)->with($relationships);
    }

    /**
     * Get store by ID.
     *
     * @param string $storeId
     * @param array $relationships
     * @return Store|null
     */
    public function getStoreById(string $storeId, array $relationships = []): Store|null
    {
        return $this->queryStoreById($storeId, $relationships)->first();
    }

    /**
     * Query stores by IDs.
     *
     * @param string $storeId
     * @param array $relationships
     * @return Builder|Relation
     */
    public function queryStoresByIds(string $storeIds, array $relationships = []): Builder|Relation
    {
        return $this->query->whereIn('stores.id', $storeIds)->with($relationships);
    }

    /**
     * Get stores by IDs.
     *
     * @param string $storeId
     * @param array $relationships
     * @return Collection
     */
    public function getStoresByIds(string $storeIds, array $relationships = []): Collection
    {
        return $this->queryStoresByIds($storeIds, $relationships)->get();
    }

    /**
     * Add creator.
     *
     * @param Store $store
     * @param User $user
     * @return void
     */
    public function addStoreCreator(Store $store, $user)
    {
        $this->addTeamMembers($store, $user, TeamMemberStatus::JOINED, ['*'], TeamMemberRole::CREATOR);
    }

    /**
     * Add admins.
     *
     * @param Store $store
     * @param Collection|User[]|User $users
     * @return void
     */
    public function addAdmins(Store $store, $users = [])
    {
        $this->addTeamMembers($store, $users, TeamMemberStatus::INVITED, ['*'], TeamMemberRole::ADMIN);
    }

    /**
     * Add team members.
     *
     * @param Store $store
     * @param Collection|User[]|User $users
     * @param TeamMemberStatus $teamMemberStatus
     * @param array<string> $teamMemberPermissions
     * @param TeamMemberRole|null $teamMemberRole
     * @return void
     */
    public function addTeamMembers(Store $store, $users, TeamMemberStatus $teamMemberStatus, array $teamMemberPermissions, TeamMemberRole|null $teamMemberRole = null)
    {
        if(($user = $users) instanceof User) {
            $users = collect([$user]);
        }elseif(is_array($users)) {
            $users = collect($users);
        }

        $userIds = $users->pluck('id');

        if( $userIds->count() ) {

            $records = [];
            $teamMemberPermissions = $this->normalizePermissions($teamMemberPermissions);
            $invitedByUser = $teamMemberStatus == TeamMemberStatus::INVITED ? request()->current_user : null;
            $teamMemberRole = $teamMemberRole ?? $this->determineRoleBasedOnPermissions($teamMemberPermissions);

            $lastSeenAt = $teamMemberRole == TeamMemberRole::CREATOR ? now() : null;
            $followerStatus = $teamMemberRole == TeamMemberRole::CREATOR ? FollowerStatus::FOLLOWING->value : null;
            $existingUserStoreAssociations = DB::table('user_store_association')->where(['store_id' => $store->id])->get();

            foreach($userIds as $userId) {

                $matchingUserStoreAssociation = $existingUserStoreAssociations->firstWhere(fn($existingUserStoreAssociation) => $existingUserStoreAssociation->user_id == $userId);

                if($matchingUserStoreAssociation) {

                    DB::table('user_store_association')->where([
                        'store_id' => $store->id,
                        'user_id' => $userId
                    ])->update([
                        'team_member_permissions' => json_encode($teamMemberPermissions),
                        'invited_to_join_team_by_user_id' => $invitedByUser->id ?? null,
                        'team_member_status' => $teamMemberStatus->value,
                        'team_member_role' => $teamMemberRole->value,
                        'updated_at' => now()
                    ]);

                }else{

                    $record = [
                        'team_member_permissions' => json_encode($teamMemberPermissions),
                        'invited_to_join_team_by_user_id' => $invitedByUser->id ?? null,
                        'team_member_status' => $teamMemberStatus->value,
                        'team_member_role' => $teamMemberRole->value,
                        'follower_status' => $followerStatus,
                        'last_seen_at' => $lastSeenAt,
                        'store_id' => $store->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'user_id' => $userId,
                        'id' => Str::uuid()
                    ];

                    $records[] = $record;

                }

                //  Clear cache
                $this->clearCacheOnAssociationAsFollower($userId);
                $this->clearCacheOnAssociationAsTeamMember($userId);
                $this->clearCacheOnAssociationAsRecentVisitor($userId);

            }

            DB::table('user_store_association')->insert($records);

            if($teamMemberRole !== TeamMemberRole::CREATOR) {
                Notification::send($users, new InvitationToJoinStoreTeamCreated($store, $invitedByUser));
            }

        }
    }

    /**
     * Add team members by mobile numbers.
     *
     * @param Store $store
     * @param string|array<string> $mobileNumbers
     * @param array<string> $teamMemberPermissions
     * @param TeamMemberRole|null $teamMemberRole
     * @return void
     */
    public function addTeamMembersByMobileNumbers(Store $store, $mobileNumbers, array $teamMemberPermissions, TeamMemberRole|null $teamMemberRole = null)
    {
        if(is_int($mobileNumber = $mobileNumbers)) {
            $mobileNumbers = collect([$mobileNumber]);
        }elseif(is_array($mobileNumbers)) {
            $mobileNumbers = collect($mobileNumbers);
        }

        if( $mobileNumbers->count() ) {

            $records = [];
            $teamMemberPermissions = $this->normalizePermissions($teamMemberPermissions);
            $teamMemberRole = $teamMemberRole ?? $this->determineRoleBasedOnPermissions($teamMemberPermissions);
            $existingUserStoreAssociations = DB::table('user_store_association')->where(['store_id' => $store->id])->get();

            foreach($mobileNumbers->toArray() as $mobileNumber) {

                $matchingUserStoreAssociation = $existingUserStoreAssociations->firstWhere(fn($existingUserStoreAssociation) => $existingUserStoreAssociation->mobile_number == $mobileNumber);

                if($matchingUserStoreAssociation) {

                    DB::table('user_store_association')->where([
                        'mobile_number' => $mobileNumber,
                        'store_id' => $store->id
                    ])->update([
                        'team_member_permissions' => json_encode($teamMemberPermissions),
                        'invited_to_join_team_by_user_id' => $invitedByUser->id ?? null,
                        'team_member_status' => TeamMemberStatus::INVITED,
                        'team_member_role' => $teamMemberRole->value,
                        'updated_at' => now()
                    ]);

                }else{

                    $record = [
                        'team_member_permissions' => json_encode($teamMemberPermissions),
                        'invited_to_join_team_by_user_id' => request()->current_user->id,
                        'user_id' => $this->getUserRepository()->getGuestUserId(),
                        'team_member_status' => TeamMemberStatus::INVITED,
                        'team_member_role' => $teamMemberRole->value,
                        'mobile_number' => $mobileNumber,
                        'store_id' => $store->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'id' => Str::uuid()
                    ];

                    $records[] = $record;

                }

            }

            DB::table('user_store_association')->insert($records);

        }
    }

    /**
     * Normalize permissions.
     *
     * @param array<string> $teamMemberPermissions
     * @return array<string>
     */
    public function normalizePermissions(array $teamMemberPermissions): array
    {
        return collect($teamMemberPermissions)->contains('*') ? ["*"] : $teamMemberPermissions;
    }

    /**
     * Determine role based on permissions.
     *
     * @param array<string> $teamMemberPermissions
     * @return TeamMemberRole
     */
    public function determineRoleBasedOnPermissions(array $teamMemberPermissions): TeamMemberRole
    {
        return collect($teamMemberPermissions)->contains('*') ? TeamMemberRole::ADMIN : TeamMemberRole::TEAM_MEMBER;
    }

    /**
     * Add followers.
     *
     * @param Store $store
     * @param Collection|User[]|User $users
     * @param FollowerStatus $followerStatus
     * @return void
     */
    public function addFollowers(Store $store, $users, FollowerStatus $followerStatus)
    {
        if(($user = $users) instanceof User) {
            $users = collect([$user]);
        }elseif(is_array($users)) {
            $users = collect($users);
        }

        $userIds = $users->pluck('id');

        if( $userIds->count() ) {

            $records = [];
            $invitedByUser = $followerStatus == FollowerStatus::INVITED ? request()->current_user : null;
            $existingUserStoreAssociations = DB::table('user_store_association')->where(['store_id' => $store->id])->get();

            foreach($userIds as $userId) {

                $matchingUserStoreAssociation = $existingUserStoreAssociations->firstWhere(fn($existingUserStoreAssociation) => $existingUserStoreAssociation->user_id == $userId);

                if($matchingUserStoreAssociation) {

                    DB::table('user_store_association')->where([
                        'store_id' => $store->id,
                        'user_id' => $userId
                    ])->update([
                        'invited_to_follow_by_user_id' => $invitedByUser->id ?? null,
                        'follower_status' => $followerStatus->value,
                        'updated_at' => now(),
                        'user_id' => $userId
                    ]);

                }else{

                    $record = [
                        'invited_to_follow_by_user_id' => $invitedByUser->id ?? null,
                        'follower_status' => $followerStatus->value,
                        'store_id' => $store->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'user_id' => $userId,
                        'id' => Str::uuid()
                    ];

                    $records[] = $record;

                }

                //  Clear cache
                $this->clearCacheOnAssociationAsFollower($userId);
            }

            DB::table('user_store_association')->insert($records);
            Notification::send($users, new InvitationToFollowStoreCreated($store, $invitedByUser));

        }
    }

    /**
     * Add followers by mobile numbers.
     *
     * @parm Store $store
     * @param string|array<string> $mobileNumbers
     * @return void
     */
    public function addFollowersByMobileNumbers(Store $store, $mobileNumbers)
    {
        if(is_int($mobileNumber = $mobileNumbers)) {
            $mobileNumbers = collect([$mobileNumber]);
        }elseif(is_array($mobileNumbers)) {
            $mobileNumbers = collect($mobileNumbers);
        }

        if( $mobileNumbers->count() ) {

            $records = $mobileNumbers->map(function($mobileNumber) use ($store) {

                return [
                    'invited_to_follow_by_user_id' => request()->current_user->id,
                    'user_id' => $this->getUserRepository()->getGuestUserId(),
                    'follower_status' => FollowerStatus::INVITED,
                    'mobile_number' => $mobileNumber,
                    'store_id' => $store->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'id' => Str::uuid()
                ];

            })->toArray();

            DB::table('user_store_association')->insert($records);

        }
    }

    /**
     * Get user store association.
     *
     * @param Store $store
     * @param string|null $userId
     * @return UserStoreAssociation|null
     */
    public function getUserStoreAssociation(Store $store, $userId = null)
    {
        return UserStoreAssociation::where('user_id', $userId ?? request()->current_user->id)
                                   ->where('store_id', $store->id)->first();
    }

    /**
     * Check if associated as store creator.
     *
     * @param Store $store
     * @return bool
     */
    public function checkIfAssociatedAsStoreCreator(Store $store)
    {
        $userStoreAssociation = $this->getUserStoreAssociation($store);
        return $userStoreAssociation && $userStoreAssociation->is_team_member_as_creator;
    }

    /**
     * Check if associated as store creator or admin.
     *
     * @param Store $store
     * @return bool
     */
    public function checkIfAssociatedAsStoreCreatorOrAdmin(Store $store, UserStoreAssociation|null $userStoreAssociation = null)
    {
        $userStoreAssociation = $userStoreAssociation ?? $this->getUserStoreAssociation($store);
        return $userStoreAssociation && $userStoreAssociation->is_team_member_as_creator_or_admin;
    }

    /**
     * Check if associated as store team member.
     *
     * @param Store $store
     * @return bool
     */
    public function checkIfAssociatedAsStoreTeamMember(Store $store)
    {
        $userStoreAssociation = $this->getUserStoreAssociation($store);
        return $userStoreAssociation && $userStoreAssociation->is_team_member_who_has_joined;
    }

    /**
     * Clear cache on association as follower.
     *
     * @param string $userId
     * @return void
     */
    public function clearCacheOnAssociationAsFollower($userId)
    {
        $cacheNames = [
            CacheName::TOTAL_STORES_AS_FOLLOWER
        ];

        foreach($cacheNames as $cacheName) {
            (new CacheManager($cacheName))->append($userId)->forget();
        }
    }

    /**
     * Clear cache on association as team member.
     *
     * @param string $userId
     * @return void
     */
    public function clearCacheOnAssociationAsTeamMember($userId)
    {
        $cacheNames = [
            CacheName::TOTAL_STORES_JOINED_AS_NON_CREATOR,
            CacheName::TOTAL_STORES_JOINED_AS_TEAM_MEMBER,
            CacheName::TOTAL_STORES_INVITED_TO_JOIN_AS_TEAM_MEMBER
        ];

        foreach($cacheNames as $cacheName) {
            (new CacheManager($cacheName))->append($userId)->forget();
        }
    }

    /**
     * Clear cache on association as recent visitor.
     *
     * @param string $userId
     * @return void
     */
    public function clearCacheOnAssociationAsRecentVisitor($userId)
    {
        $cacheNames = [
            CacheName::TOTAL_STORES_AS_RECENT_VISITOR
        ];

        foreach($cacheNames as $cacheName) {
            (new CacheManager($cacheName))->append($userId)->forget();
        }
    }

    /**
     * Notify store team members on user response to accept or decline invitation to follow store(s).
     *
     * @param InvitationResponse $invitationResponse
     * @param Store|Collection|array<Store> $storesInvitedToFollow
     * @return void
     */
    private function notifyStoreTeamMembersOnUserResponseToFollowInvitation(InvitationResponse $invitationResponse, $storesInvitedToFollow): void
    {
        $sendNotifications = function($storeInvitedToFollow) use ($invitationResponse) {
            if($invitationResponse == InvitationResponse::ACCEPTED) {
                Notification::send($storeInvitedToFollow->teamMembers,new InvitationToFollowStoreAccepted($storeInvitedToFollow, request()->current_user));
            }else{
                Notification::send($storeInvitedToFollow->teamMembers,new InvitationToFollowStoreDeclined($storeInvitedToFollow, request()->current_user));
            }
        };

        if($storesInvitedToFollow instanceof Store) {
            $storesInvitedToFollow = [$storesInvitedToFollow];
        }

        foreach($storesInvitedToFollow as $storeInvitedToFollow) {
            $sendNotifications($storeInvitedToFollow);
        }
    }

    /**
     * Notify store team members on user response to accept or decline invitation to join store(s).
     *
     * @param InvitationResponse $invitationResponse
     * @param Store|Collection|array<Store> $storesInvitedToJoin
     * @return void
     */
    private function notifyStoreTeamMembersOnUserResponseToJoinInvitation(InvitationResponse $invitationResponse, $storesInvitedToJoin): void
    {
        $sendNotifications = function($storeInvitedToJoin) use ($invitationResponse) {
            if($invitationResponse == InvitationResponse::ACCEPTED) {
                Notification::send($storeInvitedToJoin->teamMembers,new InvitationToJoinStoreTeamAccepted($storeInvitedToJoin, request()->current_user));
            }else{
                Notification::send($storeInvitedToJoin->teamMembers,new InvitationToJoinStoreTeamDeclined($storeInvitedToJoin, request()->current_user));
            }
        };

        if($storesInvitedToJoin instanceof Store) {
            $storesInvitedToJoin = [$storesInvitedToJoin];
        }

        foreach($storesInvitedToJoin as $storeInvitedToJoin) {
            $sendNotifications($storeInvitedToJoin);
        }
    }

    /**
     * Get assigned team members.
     *
     * @param Store $store
     * @param array $mobileNumbers
     * @return Collection
     */
    private function getAssignedTeamMembers(Store $store, array $mobileNumbers): Collection
    {
        return $store->teamMembers()->whereIn('users.mobile_number', $mobileNumbers)->get();
    }

    /**
     * Get assigned followers.
     *
     * @param Store $store
     * @param array $mobileNumbers
     * @return Collection
     */
    private function getAssignedFollowers(Store $store, array $mobileNumbers): Collection
    {
        return $store->followers()->whereIn('users.mobile_number', $mobileNumbers)->get();
    }

    /**
     * Get non assigned team members.
     *
     * @param Store $store
     * @param array $mobileNumbers
     * @return Collection
     */
    private function getNotAssignedTeamMembers(Store $store, array $mobileNumbers): Collection
    {
        return User::whereIn('mobile_number', $mobileNumbers)
            ->whereDoesntHave('storesAsTeamMember', function (Builder $query) use ($store) {
                $query->where('user_store_association.store_id', $store->id);
            })->get();
    }

    /**
     * Get non assigned followers.
     *
     * @param Store $store
     * @param array $mobileNumbers
     * @return Collection
     */
    private function getNotAssignedFollowers(Store $store, array $mobileNumbers): Collection
    {
        return User::whereIn('mobile_number', $mobileNumbers)
            ->whereDoesntHave('storesAsFollower', function (Builder $query) use ($store) {
                $query->where('user_store_association.store_id', $store->id);
            })->get();
    }

    /**
     * Get non-existing team members but invited.
     *
     * @param Store $store
     * @param array $mobileNumbers
     * @return array
     */
    private function getNonExistingTeamMembersButInvited(Store $store, array $mobileNumbers): array
    {
        return DB::table('user_store_association')
            ->where('store_id', $store->id)
            ->whereIn('mobile_number', $mobileNumbers)
            ->where('team_member_status', TeamMemberStatus::INVITED)
            ->pluck('mobile_number')
            ->toArray();
    }

    /**
     * Get non-existing followers but invited.
     *
     * @param Store $store
     * @param array $mobileNumbers
     * @return array
     */
    private function getNonExistingFollowersButInvited(Store $store, array $mobileNumbers): array
    {
        return DB::table('user_store_association')
            ->where('store_id', $store->id)
            ->whereIn('mobile_number', $mobileNumbers)
            ->where('follower_status', FollowerStatus::INVITED)
            ->pluck('mobile_number')
            ->toArray();
    }

    /**
     * Get non matching mobile numbers.
     *
     * @param array $mobileNumbers
     * @param array $unmatchableMobileNumbers
     * @param Collection $assignedUsers
     * @return array
     */
    private function getNonMatchingMobileNumbers(array $mobileNumbers, array $unmatchableMobileNumbers): array
    {
        return array_diff($mobileNumbers, $unmatchableMobileNumbers);
    }

    /**
     * Prepare invitation message.
     *
     * @param array $mobileNumbers
     * @param Collection $assignedUsers
     * @return string
     */
    private function prepareInvitationMessage(array $mobileNumbers, Collection $assignedUsers): string
    {
        if(count($mobileNumbers) === $assignedUsers->count()) {
            $message = $assignedUsers->pluck('first_name')->join(', ', ' and ');
            $message .= $assignedUsers->count() === 1 ? ' has' : ' have';
            return "$message already been invited";
        }

        return 'Invitations sent successfully';
    }

    /**
     * Prepare invitation summary.
     *
     * @param Collection $notAssignedUsers
     * @param Collection $assignedUsers
     * @param array $mobileNumbersThatDontMatchAnyUser
     * @param array $mobileNumbersThatDontMatchAnyUserButInvited
     * @return array
     */
    private function prepareInvitationSummary(Collection $notAssignedUsers, Collection $assignedUsers, array $mobileNumbersThatDontMatchAnyUser, array $mobileNumbersThatDontMatchAnyUserButInvited): array
    {
        $transformExistingUser = fn(User $user) => [
            'name' => $user->name,
            'mobile_number' => $user->mobile_number,
            'status' => $user->user_store_association->status ?? 'Invited',
        ];

        $transformNonExistingUser = fn($mobileNumber) => [
            'mobile_number' => PhoneNumberService::formatPhoneNumber($mobileNumber),
            'status' => 'Invited'
        ];

        return [
            'total_invited' => $notAssignedUsers->count() + count($mobileNumbersThatDontMatchAnyUser),
            'total_already_invited' => $assignedUsers->count() + count($mobileNumbersThatDontMatchAnyUserButInvited),
            'existing_users_invited' => [
                'total' => $notAssignedUsers->count(),
                'existing_users' => $notAssignedUsers->map($transformExistingUser)->toArray()
            ],
            'existing_users_already_invited' => [
                'total' => $assignedUsers->count(),
                'existing_users' => $assignedUsers->map($transformExistingUser)->toArray()
            ],
            'non_existing_users_invited' => [
                'total' => count($mobileNumbersThatDontMatchAnyUser),
                'non_existing_users' => collect($mobileNumbersThatDontMatchAnyUser)->map($transformNonExistingUser)->values()->toArray()
            ],
            'non_existing_users_already_invited' => [
                'total' => count($mobileNumbersThatDontMatchAnyUserButInvited),
                'non_existing_users' => collect($mobileNumbersThatDontMatchAnyUserButInvited)->map(fn($mobileNumber) => $transformNonExistingUser($mobileNumber))->values()->toArray()
            ],
        ];
    }

    /**
     * Update invitation status to follow store.
     *
     * @param Store $store
     * @param FollowerStatus $followerStatus
     * @return void
     */
    private function updateInvitationStatusToFollowStore(Store $store, FollowerStatus $followerStatus): void
    {
        DB::table('user_store_association')
            ->where('store_id', $store->id)
            ->where('user_id', request()->current_user->id)->update([
                'follower_status' => $followerStatus->value
            ]);
    }

    /**
     * Update invitation status to join store team.
     *
     * @param Store $store
     * @param TeamMemberStatus $teamMemberStatus
     * @return void
     */
    private function updateInvitationStatusToJoinStoreTeam(Store $store, TeamMemberStatus $teamMemberStatus): void
    {
        DB::table('user_store_association')
            ->where('store_id', $store->id)
            ->where('user_id', request()->current_user->id)->update([
                'team_member_status' => $teamMemberStatus->value
            ]);
    }

    /**
     * Extract permissions.
     *
     * @param array<string> $teamMemberPermissions
     * @return array
     */
    public function extractPermissions($teamMemberPermissions = [])
    {
        return collect($teamMemberPermissions)->contains('*')
            ? collect(Store::PERMISSIONS)->filter(fn($permission) => $permission['grant'] !== '*')->values()->toArray()
            : collect($teamMemberPermissions)->map(function($permission) {
                return collect(Store::PERMISSIONS)->filter(
                    fn($storePermission) => $storePermission['grant'] == $permission
                )->first();
            })->filter()->toArray();
    }
}
