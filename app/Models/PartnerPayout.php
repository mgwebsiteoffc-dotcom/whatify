<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id', 'amount', 'status', 'payment_method',
        'transaction_reference', 'payout_details', 'processed_at',
    ];

    protected $casts = [
        'payout_details' => 'array',
        'processed_at' => 'datetime',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}