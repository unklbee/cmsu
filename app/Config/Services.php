<?php

namespace Config;

use App\Services\CMS\CacheService;
use App\Services\CMS\CMSService;
use App\Services\CMS\ThemeService;
use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \CodeIgniter\Example();
     * }
     */
    public static function cms($getShared = true): object
    {
        if ($getShared) {
            return static::getSharedInstance('cms');
        }

        return new CMSService();
    }

    public static function cmsCache($getShared = true): object
    {
        if ($getShared) {
            return static::getSharedInstance('cmsCache');
        }

        return new CacheService();
    }

    public static function theme($getShared = true): object
    {
        if ($getShared) {
            return static::getSharedInstance('theme');
        }

        return new ThemeService();
    }
}
