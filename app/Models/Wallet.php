<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'balance', 'total_recharged', 'total_spent',
        'currency', 'auto_recharge', 'auto_recharge_amount',
        'auto_recharge_threshold',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_recharged' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'auto_recharge' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function hasBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    public function isLowBalance(): bool
    {
        return $this->balance <= config('whatify.wallet.low_balance_alert');
    }
}