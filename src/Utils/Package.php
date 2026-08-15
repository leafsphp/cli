<?php

declare(strict_types=1);

namespace Leaf\Console\Utils;

/**
 * Package
 * ----
 * Meta info on leaf cli
 */
class Package
{
    /**
     * Check current version
     */
    public static function info()
    {
        return json_decode(file_get_contents(
            dirname(__DIR__, 2) . '/composer.json'
        ));
    }

    /**
     * Check current version
     */
    public static function version()
    {
        if (\Composer\InstalledVersions::isInstalled('leafs/cli')) {
            return \Composer\InstalledVersions::getPrettyVersion('leafs/cli');
        }

        return static::info()->version ?? 'dev';
    }

    /**
     * Find latest stable version
     */
    public static function ltsInfo()
    {
        $data = @file_get_contents('https://repo.packagist.org/p2/leafs/cli.json', false, stream_context_create([
            'http' => ['timeout' => 3],
        ]));

        if (!$data) {
            return static::info();
        }

        $package = json_decode($data);

        return $package->packages->{'leafs/cli'}[0];
    }

    /**
     * Find latest stable version
     */
    public static function ltsVersion()
    {
        $package = static::ltsInfo();

        // the offline fallback is the local composer.json, which carries
        // no version field — treat that as "no update available"
        return $package->version ?? static::version();
    }

    /**
     * Check if there is an update available
     */
    public static function updateAvailable()
    {
        $currentVersion = ltrim((string) static::version(), 'v');

        // dev checkouts (contributors, CI) never self-update — this also
        // keeps the check off the network for test runs
        if ($currentVersion === '' || strpos($currentVersion, 'dev') !== false) {
            return false;
        }

        $latestVersion = ltrim((string) static::ltsVersion(), 'v');

        return version_compare($currentVersion, $latestVersion, '<');
    }
}
