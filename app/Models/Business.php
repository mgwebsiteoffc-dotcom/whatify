<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'company_name', 'display_name', 'industry',
        'website', 'logo', 'description', 'address', 'city',
        'state', 'country', 'pincode', 'gstin', 'pan',
        'business_size', 'settings', 'status',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function whatsappAccounts()
    {
        return $this->hasMany(WhatsappAccount::class);
    }
}