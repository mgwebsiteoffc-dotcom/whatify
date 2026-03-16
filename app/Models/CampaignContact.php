<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id', 'contact_id', 'message_id',
        'status', 'error_message', 'variables',
    ];

    protected $casts = [
        'variables' => 'array',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}