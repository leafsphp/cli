<?php

declare(strict_types=1);

namespace Leaf\Console\Utils;

use Symfony\Component\Process\Process;

/**
 * Upgrade
 * ----
 * Tell the user, once, when their app is behind on Leaf majors.
 */
class Upgrade
{
    public const GUIDE = 'https://leafphp.dev/docs/upgrade-guide';

    /**
     * Build the nudge line for the app in $directory, or null when
     * everything is current or we can't tell (no lock file, offline,
     * composer error). Never throws.
     */
    public static function nudge(?string $directory = null): ?string
    {
        try {
            $directory = $directory ?? getcwd();

            if (!$directory || !is_file("$directory/composer.json")) {
                return null;
            }

            $leaf = static::lockedVersion($directory, 'leafs/leaf');

            if ($leaf !== null && version_compare($leaf, '5.0.0', '<')) {
                return "leafs/leaf $leaf installed, Leaf 5 is available. See " . static::GUIDE;
            }

            $outdated = static::outdatedLeafModules($directory);

            if (empty($outdated)) {
                return null;
            }

            $list = implode(', ', array_map(
                fn ($name, $versions) => "$name {$versions[0]} -> {$versions[1]}",
                array_keys($outdated),
                $outdated
            ));

            return "Newer Leaf majors available: $list. See " . static::GUIDE;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Read a package's installed version from composer.lock. Returns null
     * for missing packages and non-numeric (dev branch) versions.
     */
    public static function lockedVersion(string $directory, string $package): ?string
    {
        if (!is_file("$directory/composer.lock")) {
            return null;
        }

        $lock = json_decode((string) file_get_contents("$directory/composer.lock"), true);

        if (!is_array($lock)) {
            return null;
        }

        foreach (array_merge($lock['packages'] ?? [], $lock['packages-dev'] ?? []) as $installed) {
            if (($installed['name'] ?? null) === $package) {
                return static::normalize((string) ($installed['version'] ?? ''));
            }
        }

        return null;
    }

    /**
     * Direct leafs/* dependencies with a newer major, via
     * `composer outdated --direct --major-only`. Empty on any failure.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function outdatedLeafModules(string $directory): array
    {
        $process = Process::fromShellCommandline(
            Core::findComposer() . ' outdated --direct --major-only --format=json --no-interaction --no-ansi',
            $directory,
            ['COMPOSER_NO_INTERACTION' => '1']
        );
        $process->setTimeout(30);
        $process->run();

        if (!$process->isSuccessful()) {
            return [];
        }

        $report = json_decode($process->getOutput(), true);
        $found = [];

        foreach ($report['installed'] ?? [] as $package) {
            $name = $package['name'] ?? '';

            if (strpos($name, 'leafs/') !== 0) {
                continue;
            }

            $current = static::normalize((string) ($package['version'] ?? ''));
            $latest = static::normalize((string) ($package['latest'] ?? ''));

            if ($current && $latest && (int) $latest > (int) $current) {
                $found[$name] = [$current, $latest];
            }
        }

        return $found;
    }

    /**
     * "v4.4.1" -> "4.4.1"; dev branches -> null.
     */
    protected static function normalize(string $version): ?string
    {
        $version = ltrim(trim($version), 'vV');

        return preg_match('/^\d+(\.\d+)*$/', $version) ? $version : null;
    }
}
