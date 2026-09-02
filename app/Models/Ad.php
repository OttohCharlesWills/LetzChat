<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Ad extends Model
{
    use HasFactory;

protected $fillable = [
    'user_id', 'post_id', 'status', 'budget', 'spent', 'cost_per_impression',
    'start_at', 'end_at', 'target_min_age', 'target_max_age',
    'target_gender', 'target_locations',
];

    protected $casts = [
        'budget'               => 'decimal:2',
        'spent'                => 'decimal:2',
        'cost_per_impression'  => 'decimal:4',
        'start_at'             => 'datetime',
        'end_at'               => 'datetime',
        'target_locations'     => 'array',
    ];

    protected static function booted()
    {
        static::creating(function (Ad $ad) {
            $ad->uuid = (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function advertiser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function remainingBudget(): float
    {
        return (float) $this->budget - (float) $this->spent;
    }

        public function isEligibleFor(User $viewer): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if (now()->lt($this->start_at) || now()->gt($this->end_at)) {
            return false;
        }

        if ($this->remainingBudget() < (float) $this->cost_per_impression) {
            return false;
        }

        if ($this->target_gender !== 'any' && $viewer->gender !== $this->target_gender) {
            return false;
        }

        if ($this->target_min_age || $this->target_max_age) {
            if (! $viewer->date_of_birth) {
                return false;
            }

            $age = $viewer->date_of_birth->age;

            if ($this->target_min_age && $age < $this->target_min_age) {
                return false;
            }
            if ($this->target_max_age && $age > $this->target_max_age) {
                return false;
            }
        }

        if (! empty($this->target_locations) && $viewer->location) {
            $matches = collect($this->target_locations)
                ->contains(fn ($loc) => str_contains(
                    mb_strtolower($viewer->location),
                    mb_strtolower($loc)
                ));

            if (! $matches) {
                return false;
            }
        }

        return true;
    }
}