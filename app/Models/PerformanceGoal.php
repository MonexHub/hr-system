<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceGoal extends Model
{
    protected $fillable = [
        'performance_appraisal_id',
        'description',
        'target_date',
        'status', //
        'progress',
        'comments',
    ];

    protected $casts = [
        'target_date' => 'date',
        'progress' => 'integer',
    ];

    public function appraisal(): BelongsTo
    {
        return $this->belongsTo(PerformanceAppraisal::class);
    }
}
