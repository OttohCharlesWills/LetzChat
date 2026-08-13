<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupPoost extends Model
{
    protected $fillable = [
        'name', 'description', 'privacy', 'cover_photo', 'created_by',
        'post_permission', 'require_post_approval',
    ];

    protected $casts = [
        'require_post_approval' => 'boolean',
    ];

    public function members()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function memberRoleFor(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        return $this->members()->where('user_id', $user->id)->value('role');
    }

    public function isAdminOrModerator(?User $user): bool
    {
        return in_array($this->memberRoleFor($user), ['admin', 'moderator'], true);
    }

    /**
     * Can this user post in the group AT ALL (ignoring approval)?
     */
    public function allowsPostingBy(?User $user): bool
    {
        $role = $this->memberRoleFor($user);

        if (! $role) {
            return false; // not a member
        }

        if ($this->post_permission === 'admin_only') {
            return in_array($role, ['admin', 'moderator'], true);
        }

        return true; // 'everyone' + is a member
    }

    /**
     * Does a post from this user need to sit in the pending queue?
     */
    public function requiresApprovalFor(User $user): bool
    {
        if ($this->isAdminOrModerator($user)) {
            return false; // admins/mods never need approval for their own posts
        }

        return $this->post_permission === 'everyone' && $this->require_post_approval;
    }
}