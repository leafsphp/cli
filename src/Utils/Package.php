<?php

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
			dirname(__DIR__, 2) . "/composer.json"
		));
	}

	/**
	 * Check current version
	 */
	public static function version()
	{
		$meta = static::info();

		return $meta->version;
	}

	/**
	 * Find latest stable version
	 */
	public static function ltsInfo()
	{
		$data = file_get_contents("https://repo.packagist.org/p2/leafs/cli.json");

		if (!$data) {
			return static::info();
		}

		$package = json_decode($data);

		return $package->packages->{"leafs/cli"}[0];
	}

	/**
	 * Find latest stable version
	 */
	public static function ltsVersion()
	{
		$package = static::ltsInfo();

		return $package->version;
	}

	/**
	 * Check if there is an update available
	 */
	public static function updateAvailable()
	{
		$currentVersion = static::version();
		$latestVersion = static::ltsVersion();

		return ($currentVersion !== $latestVersion);
	}
}
