<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'company_name', 'type', 'referral_code',
        'commission_rate', 'total_earned', 'total_paid',
        'pending_payout', 'total_referrals', 'active_customers',
        'payout_details', 'status', 'approved_at',
    ];

    protected $casts = [
        'payout_details' => 'encrypted:array',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payouts()
    {
        return $this->hasMany(PartnerPayout::class);
    }

    public function commissions()
    {
        return $this->hasMany(PartnerCommission::class);
    }

    public function referredUsers()
    {
        return User::where('partner_id', $this->user_id)->get();
    }
}