<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'whatsapp_account_id', 'name', 'template_id_meta',
        'category', 'language', 'status', 'header', 'body', 'footer',
        'buttons', 'variables', 'rejection_reason',
    ];

    protected $casts = [
        'header' => 'array',
        'buttons' => 'array',
        'variables' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function whatsappAccount()
    {
        return $this->belongsTo(WhatsappAccount::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}