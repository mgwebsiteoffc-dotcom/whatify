<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id', 'user_id', 'member_user_id', 'role',
        'permissions', 'status', 'invited_at', 'accepted_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'invited_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'member_user_id');
    }
}