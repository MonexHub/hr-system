<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'properties',
        'loggable_id',
        'loggable_type'
    ];

    protected $casts = [
        'properties' => 'array'
    ];

    /**
     * Get the parent loggable model.
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an activity
     */
    public static function log($user, $action, Model $model, array $properties = [])
    {
        return static::create([
            'user_id' => $user->id,
            'action' => $action,
            'loggable_id' => $model->id,
            'loggable_type' => get_class($model),
            'properties' => $properties
        ]);
    }

    /**
     * Scope a query to only include activities for a specific model.
     */
    public function scopeFor($query, Model $model)
    {
        return $query->where([
            'loggable_id' => $model->id,
            'loggable_type' => get_class($model)
        ]);
    }

    /**
     * Get the activity description
     */
    public function getDescriptionAttribute(): string
    {
        $userName = $this->user->name ?? 'System';

        return match($this->action) {
            'created' => "{$userName} created this record",
            'updated' => "{$userName} updated this record",
            'deleted' => "{$userName} deleted this record",
            'restored' => "{$userName} restored this record",
            'submitted' => "{$userName} submitted this request",
            'approved' => "{$userName} approved this request",
            'rejected' => "{$userName} rejected this request",
            'completed' => "{$userName} marked this as completed",
            default => "{$userName} {$this->action} this record"
        };
    }
}
