<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $table = 'user_sessions';

    protected $fillable = [
        'user_id',
        'device_id',

        'session_uuid',

        'platform',

        'started_at',
        'ended_at',

        'is_active',

        'duration_seconds',

        'entry_screen',
        'exit_screen',

        'ip_address',

        'country',
        'state',
        'city',

        'user_agent',

        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',

            'is_active' => 'boolean',

            'metadata' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}