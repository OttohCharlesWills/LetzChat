<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'privacy',
        'cover_photo',
        'created_by',
        'post_permission',
        'require_post_approval', 
        'join_approval',
    ];

    protected static function booted()
    {
        static::creating(function (Group $group) {
            $group->uuid = (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Membership rows (id, role, joined_at) — use this for anything that
     * needs the membership record itself, e.g. checking/changing a role.
     */
    public function members()
    {
        return $this->hasMany(GroupMember::class);
    }

    /**
     * The actual User models who are members — use this for display
     * (avatars, names) where you don't need the pivot/role data.
     */
    public function memberUsers()
    {
        return $this->belongsToMany(User::class, 'group_members')
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Posts made inside this group (reuses the full Post model — same
     * reactions/comments/shares system as profile posts).
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function isMember(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function isAdmin(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->members()->where('user_id', $user->id)->where('role', 'admin')->exists();
    }

    public function roleOf(?User $user): ?string
    {
        if (!$user) {
            return null;
        }

        return $this->members()->where('user_id', $user->id)->value('role');
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

public function requiresApprovalFor(User $user): bool
{
    if ($this->isAdminOrModerator($user)) {
        return false;
    }

    return $this->post_permission === 'everyone' && $this->require_post_approval;
}

public function joinRequests()
{
    return $this->hasMany(GroupJoinRequest::class);
}

public function requiresJoinApproval(): bool
{
    return $this->join_approval === 'manual';
}
}