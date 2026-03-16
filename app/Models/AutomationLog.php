<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'automation_id', 'contact_id', 'conversation_id',
        'current_step_id', 'status', 'variables',
        'execution_path', 'error_message',
        'started_at', 'completed_at',
    ];

    protected $casts = [
        'variables' => 'array',
        'execution_path' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function automation()
    {
        return $this->belongsTo(Automation::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}