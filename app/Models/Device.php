<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'user_id',

        'device_name',
        'device_type',

        'operating_system',
        'os_version',

        'browser',
        'browser_version',

        'device_identifier',
        'push_token',

        'ip_address',
        'user_agent',

        'is_trusted',
        'is_current',

        'last_login_at',
        'last_active_at',

        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'is_trusted' => 'boolean',
            'is_current' => 'boolean',

            'last_login_at' => 'datetime',
            'last_active_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}