<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'avatar',
        'created_by',
        'last_message_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function (Conversation $conversation) {
            $conversation->uuid = (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot(['role', 'last_read_at', 'left_at', 'is_muted'])
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage()
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: conversations the given user is an active (non-left) participant of.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->whereNull('conversation_participants.left_at');
        });
    }

    /**
     * For a 1:1 chat, get "the other person" relative to the given user.
     * Returns null for group chats (there isn't a single "other" person).
     */
    public function otherParticipant(int $currentUserId): ?User
    {
        if ($this->type !== 'private') {
            return null;
        }

        return $this->participants->firstWhere('id', '!=', $currentUserId);
    }
}