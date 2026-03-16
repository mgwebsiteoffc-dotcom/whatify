<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'phone', 'name', 'email', 'country_code',
        'status', 'source', 'custom_attributes',
        'last_message_at', 'opted_in_at', 'opted_out_at',
    ];

    protected $casts = [
        'custom_attributes' => 'array',
        'last_message_at' => 'datetime',
        'opted_in_at' => 'datetime',
        'opted_out_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function getFullPhoneAttribute(): string
    {
        return $this->country_code . $this->phone;
    }

    public function isOptedIn(): bool
    {
        return $this->status === 'active' && !$this->opted_out_at;
    }
}