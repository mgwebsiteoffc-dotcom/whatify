<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'conversation_id', 'contact_id',
        'whatsapp_account_id', 'campaign_id', 'template_id',
        'direction', 'type', 'content', 'media',
        'template_data', 'interactive_data', 'location_data',
        'wamid', 'status', 'error_code', 'error_message',
        'cost', 'message_category', 'is_bot_response',
        'sent_by', 'sent_at', 'delivered_at', 'read_at', 'failed_at',
    ];

    protected $casts = [
        'media' => 'array',
        'template_data' => 'array',
        'interactive_data' => 'array',
        'location_data' => 'array',
        'is_bot_response' => 'boolean',
        'cost' => 'decimal:4',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function whatsappAccount()
    {
        return $this->belongsTo(WhatsappAccount::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function template()
    {
        return $this->belongsTo(MessageTemplate::class, 'template_id');
    }

    public function isInbound(): bool
    {
        return $this->direction === 'inbound';
    }

    public function isOutbound(): bool
    {
        return $this->direction === 'outbound';
    }
}