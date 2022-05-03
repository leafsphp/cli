<?php

declare(strict_types=1);

namespace Leaf\Console\Utils;

class Core {
	/**
	 * Get the composer command for the environment.
	 *
	 * @return string
	 */
	public static function findComposer(): string
	{
		$composerPath = getcwd() . '/composer.phar';

		if (file_exists($composerPath)) {
			return '"' . PHP_BINARY . '" ' . $composerPath;
		}

		return 'composer';
	}
	
	/**
	 * Get the leaf CLI bin.
	 *
	 * @return string
	 */
	public static function findLeaf(): string
	{
		$leafPath = __DIR__ . '/../../bin/leaf';

		if (file_exists($leafPath)) {
			return '"' . PHP_BINARY . '" ' . $leafPath;
		}

		return 'leaf';
	}
}
