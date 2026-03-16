<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id', 'user_id', 'event',
        'amount', 'commission', 'status',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}