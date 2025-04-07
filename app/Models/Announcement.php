<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::creating(function ($announcement) {
            if (empty($announcement->created_by)) {
                $announcement->created_by = auth()->id();
            }
        });
    }

    protected $fillable = [
        'title',
        'content',
        'is_important',
        'icon',
        'department_id', // null for company-wide announcements
        'created_by',
    ];

    protected $casts = [
        'is_important' => 'boolean',
    ];

    /**
     * Get the department that this announcement belongs to.
     * If null, it's a company-wide announcement.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the user who created this announcement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include company-wide announcements.
     */
    public function scopeCompanyWide($query)
    {
        return $query->whereNull('department_id');
    }

    /**
     * Scope a query to only include department-specific announcements.
     */
    public function scopeDepartmental($query, $departmentId = null)
    {
        $query = $query->whereNotNull('department_id');

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        return $query;
    }

    /**
     * Scope a query to only include important announcements.
     */
    public function scopeImportant($query)
    {
        return $query->where('is_important', true);
    }

    /**
     * Check if the announcement is company-wide.
     */
    public function isCompanyWide(): bool
    {
        return is_null($this->department_id);
    }
}
