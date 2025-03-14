<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class PerformanceObjective extends Model
{
    use HasFactory;

    protected $fillable = [
        'performance_appraisal_id',
        'objective',
        'completion_date',
        'rating',
        'supervisor_feedback',
    ];

    public function performanceAppraisal(): BelongsTo
    {
        return $this->belongsTo(PerformanceAppraisal::class);
    }
}
