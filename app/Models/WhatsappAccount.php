<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsappAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'business_id', 'phone_number', 'phone_number_id',
        'waba_id', 'business_id_meta', 'display_name', 'access_token',
        'quality_rating', 'status', 'webhook_url', 'webhook_secret',
        'settings', 'connected_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'connected_at' => 'datetime',
    ];

    protected $hidden = ['access_token'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function templates()
    {
        return $this->hasMany(MessageTemplate::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }
}