<?php

namespace App\Helpers;

use App\Models\AppSettings;
use Illuminate\Support\Facades\Cache;

class SettingsHelper
{
    /**
     * Get a setting value
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember('setting_' . $key, 3600, function () use ($key, $default) {
            return AppSettings::get($key, $default);
        });
    }

    /**
     * Get app name
     */
    public static function getAppName()
    {
        return self::get('app_name', config('app.name'));
    }

    /**
     * Get the logo URL
     */
    public static function getLogo($mode = 'light')
    {
        $key = ($mode === 'dark') ? 'logo_dark' : 'logo_light';
        $logoPath = self::get($key);

        if (!$logoPath) {
            // Return default logo
            return asset('images/monexLogo.png');
        }

        return asset('storage/' . $logoPath);
    }

    /**
     * Get business hours
     */
    public static function getBusinessHours()
    {
        return [
            'start' => self::get('business_hours_start', '09:00'),
            'end' => self::get('business_hours_end', '17:00'),
        ];
    }

    /**
     * Get timezone
     */
    public static function getTimezone()
    {
        return self::get('timezone', config('app.timezone'));
    }

    /**
     * Get primary color
     */
    public static function getPrimaryColor()
    {
        return self::get('primary_color', '#10b981');
    }

    /**
     * Clear settings cache
     */
    public static function clearCache()
    {
        $settings = AppSettings::all();

        foreach ($settings as $setting) {
            Cache::forget('setting_' . $setting->key);
        }
    }
}
