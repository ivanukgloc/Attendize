<?php

namespace App\Attendize;

use Auth;
use PhpSpec\Exception\Exception;
use Illuminate\Support\Facades\Route;

class Utils
{
    /**
     * Generate a cdn asset path.
     *
     * @param string $path
     *
     * @return string
     */
    public static function cdnAsset(string $path)
    {
        $base = config('app.cdn') ?: config('app.url');
        $file = ltrim($path, '/');

        return "{$base}/{$file}";
    }

    /**
     * @return array
     */
    public static function getLogosNumber()
    {
        return [1, 2, 3, 4, 5];
    }

    /**
     * @param int $logoNumber
     * @return string
     */
    public static function logoPath($logoNumber = 1)
    {
        $logoNumber = static::getValidLogoNumber($logoNumber);

        return "/adminlte/img/avatar_{$logoNumber}.png";
    }

    /**
     * @param int $logoNumber
     * @return string
     */
    public static function getValidLogoNumber($logoNumber = 1)
    {
        return (in_array($logoNumber, static::getLogosNumber())) ? $logoNumber : 1;
    }

    /**
     * @param array|string $routes
     * @return bool
     */
    public static function checkRoute($routes)
    {
        if (is_string($routes)) {
            return Route::currentRouteName() == $routes;
        } elseif (is_array($routes)) {
            return in_array(Route::currentRouteName(), $routes);
        }

        return false;
    }
    
    /**
     * Check if the current user is registered
     *
     * @return bool
     */
    public static function isRegistered()
    {
        return Auth::check() && Auth::user()->is_registered;
    }

    /**
     * Check if the current user is confirmed
     *
     * @return bool
     */
    public static function isConfirmed()
    {
        return Auth::check() && Auth::user()->is_confirmed;
    }

    /**
     * Check if the DB has been set up
     *
     * @return bool
     */
    public static function isDatabaseSetup()
    {
        try {
            if (Schema::hasTable('accounts')) {
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Are we the cloud version of attendize or in dev enviornment?
     *
     * @return bool
     */
    public static function isAttendize()
    {
        return self::isAttendizeCloud() || self::isAttendizeDev();
    }

    /**
     * Are we the cloud version of Attendize?
     *
     * @return bool
     */
    public static function isAttendizeCloud()
    {
        return isset($_ENV['ATTENDIZE_CLOUD']) && $_ENV['ATTENDIZE_CLOUD'] == 'true';
    }

    /**
     * Are we in a dev enviornment?
     *
     * @return bool
     */
    public static function isAttendizeDev()
    {
        return isset($_ENV['ATTENDIZE_DEV']) && $_ENV['ATTENDIZE_DEV'] == 'true';
    }

    public static function isDownForMaintenance()
    {
        return file_exists(storage_path() . '/framework/down');
    }

    /**
     * Check if a user has admin access to events etc.
     *
     * @todo - This is a temp fix until user roles etc. are implemented
     * @param $object
     * @return bool
     */
    public static function userOwns($object)
    {
        if (!Auth::check()) {
            return false;
        }

        try {

            if (Auth::user()->account_id === $object->account_id) {
                return true;
            }

        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * Determine max upload size
     *
     * @return float|int
     */
    public static function file_upload_max_size()
    {
        static $max_size = -1;

        if ($max_size < 0) {
            // Start with post_max_size.
            $max_size = self::parse_size(ini_get('post_max_size'));

            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $upload_max = self::parse_size(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }

        return $max_size;
    }

    /**
     * Parses the given size
     *
     * @param $size
     * @return float
     */
    public static function parse_size($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

    /**
     * @param null $guard
     * @return string
     */
    public static function getUserRoleLabel($guard = null)
    {
        if (! Auth::guard($guard)->guest()) {
            $user = Auth::guard($guard)->user();
            if ($user->isAdmin()) {
                return 'Administrator';
            } else {
                return 'Member';
            }
        }

        return 'Anonymous';
    }
}
