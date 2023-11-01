<?php

namespace App\Traits;

trait UserStoreAssociationTrait
{
    /*
     *  Scope: Return users that have accepted the invitation to follow the store
     */
    public function scopeFollowing($query)
    {
        return $query->where('user_store_association.follower_status', 'Following');
    }

    /*
     *  Scope: Return users that have declined the invitation to follow the store
     */
    public function scopeUnfollowed($query)
    {
        return $query->where('user_store_association.follower_status', 'Unfollowed');
    }

    /*
     *  Scope: Return users that have not responded to the invitation to follow the store
     */
    public function scopeInvitedToFollow($query)
    {
        return $query->where('user_store_association.follower_status', 'Invited');
    }

    /*
     *  Scope: Return users that have declined the invitation to join the store team
     */
    public function scopeDeclinedToFollow($query)
    {
        return $query->where('user_store_association.follower_status', 'Declined');
    }

    /*
     *  Scope: Return users that have joined the store team
     */
    public function scopeJoinedTeam($query)
    {
        return $query->where('user_store_association.team_member_status', 'Joined');
    }

    /*
     *  Scope: Return users that have joined the store team as a creator
     */
    public function scopeJoinedTeamAsCreator($query)
    {
        return $query->joinedTeam()->where('user_store_association.team_member_role', 'Creator');
    }

    /*
     *  Scope: Return users that have joined the store team as a creator or admin
     */
    public function scopeJoinedTeamAsCreatorOrAdmin($query)
    {
        return $query->joinedTeam()->whereIn('user_store_association.team_member_role', ['Creator', 'Admin']);
    }

    /*
     *  Scope: Return users that have joined the store team as a non creator
     */
    public function scopeJoinedTeamAsNonCreator($query)
    {
        return $query->joinedTeam()->where('user_store_association.team_member_role', '!=', 'Creator');
    }

    /*
     *  Scope: Return users that have left the store team
     */
    public function scopeLeftTeam($query)
    {
        return $query->where('user_store_association.team_member_status', 'Left');
    }

    /*
     *  Scope: Return users that have not responded to the invitation to join the store team
     */
    public function scopeInvitedToJoinTeam($query)
    {
        return $query->where('user_store_association.team_member_status', 'Invited');
    }

    /*
     *  Scope: Return users that have declined the invitation to join the store team
     */
    public function scopeDeclinedToJoinTeam($query)
    {
        return $query->where('user_store_association.team_member_status', 'Declined');
    }
}
