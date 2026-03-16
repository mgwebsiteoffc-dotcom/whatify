<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Automation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'whatsapp_account_id', 'name', 'description',
        'trigger_type', 'trigger_config', 'status',
        'execution_count', 'sort_order', 'flow_data',
    ];

    protected $casts = [
        'trigger_config' => 'array',
        'flow_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function whatsappAccount()
    {
        return $this->belongsTo(WhatsappAccount::class);
    }

    public function steps()
    {
        return $this->hasMany(AutomationStep::class)->orderBy('sort_order');
    }

    public function logs()
    {
        return $this->hasMany(AutomationLog::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}