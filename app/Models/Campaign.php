<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'whatsapp_account_id', 'template_id', 'name',
        'description', 'status', 'audience_filter', 'template_variables',
        'scheduled_at', 'started_at', 'completed_at',
        'total_contacts', 'sent_count', 'delivered_count',
        'read_count', 'replied_count', 'failed_count',
        'total_cost', 'messages_per_second',
    ];

    protected $casts = [
        'audience_filter' => 'array',
        'template_variables' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function whatsappAccount()
    {
        return $this->belongsTo(WhatsappAccount::class);
    }

    public function template()
    {
        return $this->belongsTo(MessageTemplate::class, 'template_id');
    }

    public function contacts()
    {
        return $this->hasMany(CampaignContact::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function getDeliveryRate(): float
    {
        if ($this->sent_count === 0) return 0;
        return round(($this->delivered_count / $this->sent_count) * 100, 2);
    }

    public function getReadRate(): float
    {
        if ($this->delivered_count === 0) return 0;
        return round(($this->read_count / $this->delivered_count) * 100, 2);
    }
}