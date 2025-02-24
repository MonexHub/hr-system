<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'title',
        'content',
        'status',
        'error_message',
        'sent_at'
    ];

    protected $casts = [
        'sent_at' => 'datetime'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // Scopes
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Helper methods
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'error_message' => null
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error
        ]);
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'sent' => 'success',
            'failed' => 'danger',
            default => 'warning'
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'holiday' => 'heroicon-o-calendar',
            'birthday' => 'heroicon-o-cake',
            default => 'heroicon-o-bell'
        };
    }
}
