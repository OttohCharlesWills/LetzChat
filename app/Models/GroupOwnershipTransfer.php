<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GroupOwnershipTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'from_user_id',
        'to_user_id',
        'status',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function (GroupOwnershipTransfer $transfer) {
            $transfer->uuid = (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}