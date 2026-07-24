<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    use HasFactory;

    protected $fillable = ['group_id', 'user_id', 'role'];

    protected static function booted()
    {
        static::created(function (GroupMember $member) {
            Group::whereKey($member->group_id)->increment('members_count');
        });

        static::deleted(function (GroupMember $member) {
            Group::whereKey($member->group_id)->decrement('members_count');
        });
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}