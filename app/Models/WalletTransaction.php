<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id', 'type', 'amount', 'balance_after', 'ad_id', 'reference', 'description',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function (WalletTransaction $tx) {
            $tx->uuid = (string) Str::uuid();
        });
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }
}