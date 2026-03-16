<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'wallet_id', 'type', 'amount',
        'balance_before', 'balance_after', 'description',
        'reference_type', 'reference_id', 'status',
        'payment_gateway', 'payment_id', 'gateway_response',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}