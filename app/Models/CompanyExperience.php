<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyExperience extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'type',
        'job_title',
        'start_date',
        'salary'
    ];

    protected $casts = [
        'start_date' => 'date',
        'salary' => 'decimal:2'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
