<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Integration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'type', 'name', 'config', 'status',
        'last_synced_at', 'error_message',
    ];

    protected $casts = [
    'config' => 'array',
     'last_synced_at' => 'datetime',
];

    // protected $casts = [
    //     'config' => 'encrypted:array',
    //     'last_synced_at' => 'datetime',
    // ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->hasMany(IntegrationLog::class);
    }
}