<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndustryTemplate extends Model
{
    protected $fillable = [
        'name', 'slug', 'industry', 'description',
        'trigger_type', 'trigger_config', 'steps', 'is_active',
    ];

    protected $casts = [
        'trigger_config' => 'array',
        'steps' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeForIndustry($query, string $industry)
    {
        return $query->where('industry', $industry)->where('is_active', true);
    }
}