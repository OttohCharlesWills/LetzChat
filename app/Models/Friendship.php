<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friendship extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_id',
        'addressee_id',
        'status',
        'accepted_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    /**
     * The user who sent the request.
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * The user who received the request.
     */
    public function addressee()
    {
        return $this->belongsTo(User::class, 'addressee_id');
    }

    /**
     * Scope: only accepted friendships.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope: only pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: any friendship row (either direction) involving a given user.
     */
    public function scopeInvolvingUser($query, $userId)
    {
        return $query->where('requester_id', $userId)
            ->orWhere('addressee_id', $userId);
    }
}