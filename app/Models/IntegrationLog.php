<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration_id', 'event', 'payload',
        'response', 'status', 'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
    ];

    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }
}