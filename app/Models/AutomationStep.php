<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'automation_id', 'step_id', 'type', 'config',
        'next_step_id', 'branches', 'position_x',
        'position_y', 'sort_order',
    ];

    protected $casts = [
        'config' => 'array',
        'branches' => 'array',
    ];

    public function automation()
    {
        return $this->belongsTo(Automation::class);
    }
}