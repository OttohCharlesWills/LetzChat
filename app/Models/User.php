<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
        protected $fillable = [
            'first_name',
            'last_name',
            'username',

            'email',
            'phone',

            'gender',
            'date_of_birth',

            'bio',
            'avatar',
            'cover_photo',

            'password',

            'is_active',
            'is_banned',
            'is_online',

            'last_seen_at',

            'timezone',
            'language',
        ];

    protected static function booted()
    {
        static::creating(function ($user) {
            $user->uuid = (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'last_seen_at' => 'datetime',

            'is_active' => 'boolean',
            'is_banned' => 'boolean',
            'is_online' => 'boolean',

            'password' => 'hashed',
        ];
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function activities()
    {
        return $this->hasMany(UserActivity::class);
    }

    /**
     * Groups this user is a member of (any role).
     */
    public function groups()
    {
        return \App\Models\Group::whereHas('members', fn ($q) => $q->where('user_id', $this->id))->get();
    }

    public function isFriendsWith(User $other): bool
    {
        return \App\Models\Friendship::accepted()
            ->where(function ($q) use ($other) {
                $q->where('requester_id', $this->id)->where('addressee_id', $other->id);
            })
            ->orWhere(function ($q) use ($other) {
                $q->where('requester_id', $other->id)->where('addressee_id', $this->id);
            })
            ->exists();
    }

    /**
     * Accepted friends of this user, as a plain collection.
     * Used by the navbar's Messenger flyout, sidebars, etc.
     */
    public function friends()
    {
        $ids = \App\Models\Friendship::accepted()
            ->involvingUser($this->id)
            ->get()
            ->map(fn ($f) => $f->requester_id === $this->id ? $f->addressee_id : $f->requester_id);

        return User::whereIn('id', $ids)->get();
    }

    public function conversations()
    {
        return $this->belongsToMany(\App\Models\Conversation::class, 'conversation_participants')
            ->withPivot(['role', 'last_read_at', 'left_at', 'is_muted'])
            ->withTimestamps();
    }

    public function isFollowedBy(?User $other): bool
    {
        if (!$other) {
            return false;
        }

        return \App\Models\Follow::where('follower_id', $other->id)
            ->where('followed_id', $this->id)
            ->exists();
    }

    public function groupMemberships()
    {
        return $this->hasMany(GroupMember::class);
    }
}
