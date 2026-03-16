<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'contact_id', 'whatsapp_account_id',
        'assigned_agent_id', 'status', 'priority',
        'last_message', 'last_message_at', 'is_bot_active',
        'bot_paused_until', 'metadata',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'bot_paused_until' => 'datetime',
        'is_bot_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function whatsappAccount()
    {
        return $this->belongsTo(WhatsappAccount::class);
    }

    public function assignedAgent()
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function internalNotes()
    {
        return $this->hasMany(InternalNote::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }
}