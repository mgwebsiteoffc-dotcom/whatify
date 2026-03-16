<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'key', 'secret',
        'permissions', 'last_used_at', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = ['secret'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateKey(): string
    {
        return 'wfy_' . Str::random(40);
    }

    public static function generateSecret(): string
    {
        return Str::random(64);
    }
}