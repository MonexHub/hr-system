<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeImport extends Model
{
    protected $fillable = [
        'employee_code',
        'first_name',
        'last_name',
        'middle_name',
        'gender',
        'birthdate',
        'contract_type',
        'appointment_date',
        'job_title',
        'branch',
        'department',
        'salary',
        'email',
        'import_status',
        'import_errors',
        'processed_at',
        'batch_id',
        'row_number'
    ];

    protected $casts = [
        'birthdate' => 'date',
        'appointment_date' => 'date',
        'salary' => 'decimal:2',
        'processed_at' => 'datetime',
        'import_errors' => 'array'
    ];

    // Scopes for filtering
    public function scopePending($query)
    {
        return $query->where('import_status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('import_status', 'failed');
    }

    public function scopeProcessed($query)
    {
        return $query->where('import_status', 'processed');
    }

    public function scopeByBatch($query, $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    // Mark record as processed
    public function markAsProcessed()
    {
        $this->update([
            'import_status' => 'processed',
            'processed_at' => now()
        ]);
    }

    // Mark record as failed with errors
    public function markAsFailed(array $errors)
    {
        $this->update([
            'import_status' => 'failed',
            'import_errors' => $errors
        ]);
    }

    // Check if record can be processed
    public function canBeProcessed(): bool
    {
        return $this->import_status === 'pending' || $this->import_status === 'failed';
    }
}
