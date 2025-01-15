<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOffer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'offer_number',
        'job_application_id',
        'created_by',
        'approved_by',
        'base_salary',
        'salary_currency',
        'benefits_package',
        'additional_allowances',
        'proposed_start_date',
        'additional_terms',
        'special_conditions',
        'status',
        'valid_until',
        'sent_at',
        'responded_at',
        'rejection_reason',
        'negotiation_history',
        'internal_notes'
    ];

    protected $casts = [
        'benefits_package' => 'array',
        'additional_allowances' => 'array',
        'negotiation_history' => 'array',
        'base_salary' => 'decimal:2',
        'proposed_start_date' => 'date',
        'valid_until' => 'date',
        'sent_at' => 'datetime',
        'responded_at' => 'datetime'
    ];

    // Relationships
    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'pending_approval']);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['approved', 'sent', 'negotiating'])
            ->where('valid_until', '>=', now());
    }

    // Helpers
    public function isExpired(): bool
    {
        return $this->valid_until < now();
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'pending_approval']);
    }

    public function getTotalCompensationAttribute(): float
    {
        $allowances = collect($this->additional_allowances)->sum('amount') ?? 0;
        return $this->base_salary + $allowances;
    }
}
