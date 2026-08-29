<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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

    /**
     * The group's chat conversation (1:1, always type 'group').
     */
    public function conversation()
    {
        return $this->hasOne(Conversation::class);
    }

    /**
     * Get this group's Conversation, creating it (and attaching current
     * members) on first access. Safe to call every time you open group chat.
     */
    public function getOrCreateConversation(): Conversation
    {
        if ($this->conversation) {
            return $this->conversation;
        }

        return DB::transaction(function () {
            $conversation = Conversation::create([
                'type'       => 'group',
                'group_id'   => $this->id,
                'name'       => $this->name,
                'avatar'     => $this->cover_photo,
                'created_by' => $this->created_by,
            ]);

            $attach = $this->memberUsers()->pluck('users.id')
                ->mapWithKeys(fn ($userId) => [$userId => ['role' => 'member']])
                ->all();

            if (! empty($attach)) {
                $conversation->participants()->attach($attach);
            }

            $this->setRelation('conversation', $conversation);

            return $conversation;
        });
    }

    /**
     * Re-sync the group chat's participants to match current group_members.
     * Call this right after you attach/detach a row in group_members —
     * i.e. at the end of your join, leave, approve-request, and remove-member
     * flows — so the chat's membership never drifts from the group's.
     *
     * No-ops if the conversation hasn't been created yet (first chat open
     * will attach everyone anyway).
     */
    public function syncConversationParticipants(): void
    {
        if (! $this->conversation) {
            return;
        }

        $currentMemberIds = $this->memberUsers()->pluck('users.id')->all();

        $participantIds = $this->conversation->participants()
            ->wherePivotNull('left_at')
            ->pluck('users.id')
            ->all();

        $toAdd = array_diff($currentMemberIds, $participantIds);
        $toRemove = array_diff($participantIds, $currentMemberIds);

        if (! empty($toAdd)) {
            $attach = collect($toAdd)
                ->mapWithKeys(fn ($userId) => [$userId => ['role' => 'member']])
                ->all();

            $this->conversation->participants()->syncWithoutDetaching($attach);
        }

        if (! empty($toRemove)) {
            $this->conversation->participants()->updateExistingPivot($toRemove, [
                'left_at' => now(),
            ]);
        }
    }

        public function ownershipTransfers()
    {
        return $this->hasMany(GroupOwnershipTransfer::class);
    }

    public function pendingOwnershipTransfer()
    {
        return $this->hasOne(GroupOwnershipTransfer::class)->where('status', 'pending')->latestOfMany();
    }

    public function owner(): ?User
    {
        return $this->memberUsers()->wherePivot('role', 'admin')->first();
    }
}