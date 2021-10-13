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
	 * Check if there is an update available
	 */
	public static function updateAvailable()
	{
		$leafCli = json_decode(file_get_contents(dirname(dirname(__DIR__)) . "/composer.json"));
		$latestLeafCli = json_decode(file_get_contents("https://repo.packagist.org/p2/leafs/cli.json"));

		$currentVersion = $leafCli->version;
		$latestVersion = $latestLeafCli->packages->{"leafs/cli"}[0]->version;

		return ($currentVersion !== $latestVersion);
	}
}

