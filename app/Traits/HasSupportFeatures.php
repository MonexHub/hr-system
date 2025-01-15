<?php

namespace App\Traits;

use App\Models\Activity;
use App\Models\Comment;
use App\Models\CustomNotification;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasSupportFeatures
{
    /**
     * Get all comments for the model.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get all activities for the model.
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'loggable');
    }

    /**
     * Get all notifications for the model.
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(CustomNotification::class, 'notifiable');
    }

    /**
     * Add a comment to the model.
     */
    public function addComment(string $content, $userId = null): Comment
    {
        return $this->comments()->create([
            'content' => $content,
            'user_id' => $userId ?? auth()->id()
        ]);
    }

    /**
     * Log an activity for the model.
     */
    public function logActivity(string $action, array $properties = []): Activity
    {
        return Activity::log(
            auth()->user(),
            $action,
            $this,
            $properties
        );
    }

    /**
     * Create a notification for the model.
     */
    public function createNotification(
        $user,
        string $type,
        string $message,
        array $data = []
    ): CustomNotification {
        return CustomNotification::notify(
            $user,
            $type,
            $message,
            $this,
            $data
        );
    }

    /**
     * Boot the trait.
     */
    protected static function bootHasSupportFeatures(): void
    {
        // Log activities automatically for common events
        static::created(function ($model) {
            $model->logActivity('created');
        });

        static::updated(function ($model) {
            $model->logActivity('updated', [
                'changed' => $model->getDirty()
            ]);
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted');
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                $model->logActivity('restored');
            });
        }
    }
}
