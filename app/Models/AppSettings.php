<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Traits\HasRoles;

class AppSettings extends Model
{
    use HasFactory;
    use HasRoles;

    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
        'group',
        'is_public',
    ];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, $default = null)
    {
        // Prevent querying a non-existent table
        if (!Schema::hasTable('settings')) {
            return $default;
        }

        $setting = self::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, $value, string $group = 'general', bool $isPublic = false)
    {
        if (!Schema::hasTable('settings')) {
            return false; // Avoid errors if table doesn't exist
        }

        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'is_public' => $isPublic,
            ]
        );
    }

    /**
     * Get all settings.
     */
    public static function allSettings()
    {
        if (!Schema::hasTable('settings')) {
            return collect(); // Return an empty collection if table doesn't exist
        }

        return self::all();
    }

    /**
     * Delete a setting by key.
     */
    public static function remove(string $key)
    {
        if (!Schema::hasTable('settings')) {
            return false;
        }

        return self::where('key', $key)->delete();
    }
}
